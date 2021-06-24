<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\JWTPayloadExtractor;

final class Base64HeaderExtractor extends AbstractExtractor
{
    public function __construct(string $issuer, string $item)
    {
        parent::__construct($issuer, ExtractorInterface::IN_HEADER, $item);
    }

    protected function extractFromValue(string $value): ?array
    {
        return $this->extractFromBase64EncodedPayload($value);
    }
}
