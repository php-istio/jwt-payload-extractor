<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\JWTPayloadExtractor;

use Psr\Http\Message\ServerRequestInterface;

final class CompositeExtractor implements ExtractorInterface
{
    public function __construct(private iterable $extractors)
    {
    }

    public function extract(ServerRequestInterface $request): ?array
    {
        foreach ($this->extractors as $extractor) {
            /** @var ExtractorInterface $extractor */
            if ($payload = $extractor->extract($request)) {
                return $payload;
            }
        }

        return null;
    }
}
