<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\JWTPayloadExtractor;

class ExtractorFactory
{
    public static function fromBase64Header(string $issuer, string $header): Base64HeaderExtractor
    {
        return new Base64HeaderExtractor($issuer, $header);
    }

    public static function fromOriginTokenHeader(string $issuer, string $header): OriginTokenExtractor
    {
        return new OriginTokenExtractor($issuer, ExtractorInterface::IN_HEADER, $header);
    }

    public static function fromOriginTokenQueryParam(string $issuer, string $queryParam): OriginTokenExtractor
    {
        return new OriginTokenExtractor($issuer, ExtractorInterface::IN_QUERY_PARAM, $queryParam);
    }

    public static function fromExtractors(ExtractorInterface ...$extractors): CompositeExtractor
    {
        return new CompositeExtractor($extractors);
    }
}
