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
    public static function fromBase64Header(string $issuer, string $header): ExtractorInterface
    {
        return new Base64HeaderExtractor($issuer, $header);
    }

    public static function fromOriginTokenHeader(string $issuer, string $header = 'Authorization', string $prefix = 'Bearer '): ExtractorInterface
    {
        return new OriginTokenExtractor($issuer, AbstractExtractor::IN_HEADER, $header, $prefix);
    }

    public static function fromOriginTokenQueryParam(string $issuer, string $queryParam, string $prefix = ''): ExtractorInterface
    {
        return new OriginTokenExtractor($issuer, AbstractExtractor::IN_QUERY_PARAM, $queryParam, $prefix);
    }

    public static function fromExtractors(ExtractorInterface ...$extractors): ExtractorInterface
    {
        return new CompositeExtractor($extractors);
    }
}
