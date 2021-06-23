<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\JWTPayloadExtractor;

final class OriginTokenExtractor extends AbstractExtractor
{
    public function __construct(string $issuer, string $requestBag, string $itemName)
    {
        if ('headers' !== $requestBag && 'query' !== $requestBag) {
            throw new \LogicException(sprintf('Invalid request bag `%s`! Origin token must in: `headers` or `query` bag.', $requestBag));
        }

        parent::__construct($issuer, $requestBag, $itemName);
    }

    protected function extractFromValue(string $value): ?array
    {
        $tokenParts = explode('.', $value, 3);

        if (3 !== count($tokenParts)) {
            return null;
        }

        return $this->extractFromBase64EncodedPayload($tokenParts[1]);
    }
}
