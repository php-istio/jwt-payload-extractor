<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\JWTPayloadExtractor\Tests;

use Istio\JWTPayloadExtractor\OriginTokenExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class OriginTokenExtractorTest extends TestCase
{
    use RequestDataProviderTrait;

    public function testInitWithBlankIssuer(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('~can not be blank!~');

        new OriginTokenExtractor('', 'headers', 'Authorization');
    }

    public function testInitWithInvalidRequestBag(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('~`headers` or `query`~');

        new OriginTokenExtractor('valid', 'invalid bag', 'Authorization');
    }

    /**
     * @dataProvider invalidRequests
     */
    public function testExtractFromInvalidRequests(Request $inHeader, Request $inQueryParam): void
    {
        [$payloadFromHeader, $payloadFromQueryParam] = $this->extractRequests($inHeader, $inQueryParam);

        $this->assertNull($payloadFromHeader);
        $this->assertNull($payloadFromQueryParam);
    }

    /**
     * @dataProvider validRequests
     */
    public function testExtractFromValidRequests(Request $inHeader, Request $inQueryParam)
    {
        [$payloadFromHeader, $payloadFromQueryParam] = $this->extractRequests($inHeader, $inQueryParam);

        $this->assertIsArray($payloadFromHeader);
        $this->assertSame('valid', $payloadFromHeader['iss']);
        $this->assertIsArray($payloadFromQueryParam);
        $this->assertSame('valid', $payloadFromQueryParam['iss']);
    }

    private function extractRequests(Request $inHeader, Request $inQueryParam): array
    {
        $headerExtractor = new OriginTokenExtractor('valid', 'headers', 'Authorization');
        $queryParamExtractor = new OriginTokenExtractor('valid', 'query', 'token');

        return [$headerExtractor->extract($inHeader), $queryParamExtractor->extract($inQueryParam)];
    }

    protected function getValidToken(): string
    {
        return sprintf('header.%s.signature', base64_encode(json_encode(['iss' => 'valid'])));
    }

    protected function getInvalidToken(): string
    {
        return sprintf('header.%s.signature', base64_encode(json_encode(['iss' => 'invalid'])));
    }
}
