<?php

declare(strict_types=1);

namespace Shlinkio\Shlink;

use GuzzleHttp\Client;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ServiceManager\Factory\InvokableFactory;
use PDO;

use function Shlinkio\Shlink\Common\env;
use function sprintf;
use function sys_get_temp_dir;

$swooleTestingHost = '127.0.0.1';
$swooleTestingPort = 9999;

$buildDbConnection = function (): array {
    $driver = env('DB_DRIVER', 'sqlite');
    $isCi = env('TRAVIS', false);
    $getMysqlHost = fn (string $driver) => sprintf('shlink_db%s', $driver === 'mysql' ? '' : '_maria');
    $getCiMysqlPort = fn (string $driver) => $driver === 'mysql' ? '3307' : '3308';

    $driverConfigMap = [
        'sqlite' => [
            'driver' => 'pdo_sqlite',
            'path' => sys_get_temp_dir() . '/shlink-tests.db',
        ],
        'mysql' => [
            'driver' => 'pdo_mysql',
            'host' => $isCi ? '127.0.0.1' : $getMysqlHost($driver),
            'port' => $isCi ? $getCiMysqlPort($driver) : '3306',
            'user' => 'root',
            'password' => 'root',
            'dbname' => 'shlink_test',
            'charset' => 'utf8',
            'driverOptions' => [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            ],
        ],
        'postgres' => [
            'driver' => 'pdo_pgsql',
            'host' => $isCi ? '127.0.0.1' : 'shlink_db_postgres',
            'port' => $isCi ? '5433' : '5432',
            'user' => 'postgres',
            'password' => 'root',
            'dbname' => 'shlink_test',
            'charset' => 'utf8',
        ],
        'mssql' => [
            'driver' => 'pdo_sqlsrv',
            'host' => $isCi ? '127.0.0.1' : 'shlink_db_ms',
            'user' => 'sa',
            'password' => 'Passw0rd!',
            'dbname' => 'shlink_test',
        ],
    ];
    $driverConfigMap['maria'] = $driverConfigMap['mysql'];

    return $driverConfigMap[$driver] ?? [];
};

return [

    'debug' => true,
    ConfigAggregator::ENABLE_CACHE => false,

    'url_shortener' => [
        'domain' => [
            'schema' => 'http',
            'hostname' => 'doma.in',
        ],
        'validate_url' => true,
    ],

    'mezzio-swoole' => [
        'enable_coroutine' => false,
        'swoole-http-server' => [
            'host' => $swooleTestingHost,
            'port' => $swooleTestingPort,
            'process-name' => 'shlink_test',
            'options' => [
                'pid_file' => sys_get_temp_dir() . '/shlink-test-swoole.pid',
                'enable_coroutine' => false,
            ],
        ],
    ],

    'mercure' => [
        'public_hub_url' => null,
        'internal_hub_url' => null,
        'jwt_secret' => null,
    ],

    'dependencies' => [
        'services' => [
            'shlink_test_api_client' => new Client([
                'base_uri' => sprintf('http://%s:%s/', $swooleTestingHost, $swooleTestingPort),
                'http_errors' => false,
            ]),
        ],
        'factories' => [
            TestUtils\Helper\TestHelper::class => InvokableFactory::class,
        ],
    ],

    'entity_manager' => [
        'connection' => $buildDbConnection(),
    ],

    'data_fixtures' => [
        'paths' => [
            __DIR__ . '/../../module/Rest/test-api/Fixtures',
        ],
    ],

];
