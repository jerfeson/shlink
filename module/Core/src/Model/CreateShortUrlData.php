<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Core\Model;

use Psr\Http\Message\UriInterface;

final class CreateShortUrlData
{
    private UriInterface $longUrl;
    private array $tags;
    private ShortUrlMeta $meta;

    public function __construct(
        UriInterface $longUrl,
        array $tags = [],
        ?ShortUrlMeta $meta = null
    ) {
        $this->longUrl = $longUrl;
        $this->tags = $tags;
        $this->meta = $meta ?? ShortUrlMeta::createEmpty();
    }

    /**
     */
    public function getLongUrl(): UriInterface
    {
        return $this->longUrl;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     */
    public function getMeta(): ShortUrlMeta
    {
        return $this->meta;
    }
}
