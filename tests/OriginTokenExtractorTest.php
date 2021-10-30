<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\JWTPayloadExtractor\Tests;

use Istio\JWTPayloadExtractor\AbstractExtractor;
use Istio\JWTPayloadExtractor\OriginTokenExtractor;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class OriginTokenExtractorTest extends TestCase
{
    use RequestCreatorTrait;

    public function testInitWithBlankIssuer(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('~can not be blank!~');

        new OriginTokenExtractor('', 'headers', 'authorization');
    }

    public function testInitWithInvalidIn(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches(
            sprintf('~`%s` or `%s`~', AbstractExtractor::IN_HEADER, AbstractExtractor::IN_QUERY_PARAM)
        );

        new OriginTokenExtractor('valid', 'invalid', 'authorization');
    }

    /**
     * @dataProvider invalidRequests
     */
    public function testExtractFromInvalidRequests(
        ServerRequestInterface $inHeader,
        ServerRequestInterface $inQueryParam
    ): void {
        [$payloadFromHeader, $payloadFromQueryParam] = $this->extractRequests($inHeader, $inQueryParam);

        $this->assertNull($payloadFromHeader);
        $this->assertNull($payloadFromQueryParam);
    }

    public function invalidRequests(): array
    {
        return [
            [
                $this->createRequest(),
                $this->createRequest(),
            ],
            [
                $this->createRequest(headers: ['invalid_name' => '']),
                $this->createRequest(queryParams: ['invalid_name' => '']),
            ],
            [
                $this->createRequest(headers: ['authorization' => '']),
                $this->createRequest(queryParams: ['token' => '']),
            ],
            [
                $this->createRequest(headers: ['authorization' => 'Bearer invalid header']),
                $this->createRequest(queryParams: ['token' => 'invalid query params']),
            ],
            [
                $this->createRequest(headers: ['authorization' => 'Bearer=' . $this->getValidToken()]),
                $this->createRequest(queryParams: ['token' => 'Bearer..' . $this->getInvalidToken()]),
            ],
            [
                $this->createRequest(headers: ['authorization' => 'Bearer ' . $this->getInvalidToken()]),
                $this->createRequest(queryParams: ['token' => $this->getInvalidToken()]),
            ],
        ];
    }

    /**
     * @dataProvider validRequests
     */
    public function testExtractFromValidRequests(ServerRequestInterface $inHeader, ServerRequestInterface $inQueryParam)
    {
        [$payloadFromHeader, $payloadFromQueryParam] = $this->extractRequests($inHeader, $inQueryParam);

        $this->assertIsArray($payloadFromHeader);
        $this->assertSame('valid', $payloadFromHeader['iss']);
        $this->assertIsArray($payloadFromQueryParam);
        $this->assertSame('valid', $payloadFromQueryParam['iss']);
    }

    public function validRequests(): array
    {
        return [
            [
                $this->createRequest(headers: ['authorization' => 'Bearer ' . $this->getValidToken()]),
                $this->createRequest(queryParams: ['token' => $this->getValidToken()]),
            ],
        ];
    }

    private function extractRequests(ServerRequestInterface $inHeader, ServerRequestInterface $inQueryParam): array
    {
        $headerExtractor = new OriginTokenExtractor('valid', AbstractExtractor::IN_HEADER, 'authorization', 'Bearer ');
        $queryParamExtractor = new OriginTokenExtractor('valid', AbstractExtractor::IN_QUERY_PARAM, 'token');

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
