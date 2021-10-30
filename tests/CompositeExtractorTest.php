<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\JWTPayloadExtractor\Tests;

use Istio\JWTPayloadExtractor\ExtractorFactory;
use Istio\JWTPayloadExtractor\ExtractorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class CompositeExtractorTest extends TestCase
{
    use RequestCreatorTrait;

    /**
     * @dataProvider validRequests
     */
    public function testExtractFromValidRequestsWithEmptyExtractors(ServerRequestInterface $request): void
    {
        $extractor = ExtractorFactory::fromExtractors();
        $payload = $extractor->extract($request);

        $this->assertNull($payload);
    }

    /**
     * @dataProvider validRequests
     */
    public function testExtractFromValidRequests(ServerRequestInterface $request): void
    {
        $extractor = $this->getExtractor();
        $payload = $extractor->extract($request);

        $this->assertIsArray($payload);
        $this->assertSame('valid', $payload['iss']);
    }

    /**
     * @dataProvider invalidRequests
     */
    public function testExtractFromInvalidRequests(ServerRequestInterface $request): void
    {
        $extractor = $this->getExtractor();
        $payload = $extractor->extract($request);

        $this->assertNull($payload);
    }

    public function validRequests(): array
    {
        return [
            [$this->createRequest(headers: ['x-istio-jwt-payload' => $this->getValidBase64Payload()])],
            [$this->createRequest(headers: ['authorization' => 'Bearer ' . $this->getValidOriginToken()])],
            [$this->createRequest(queryParams: ['token' => $this->getValidOriginToken()])],
        ];
    }

    public function invalidRequests(): array
    {
        return [
            [$this->createRequest()],
            [$this->createRequest(headers: ['x-istio-jwt-payload' => ''])],
            [$this->createRequest(headers: ['authorization' => ''])],
            [$this->createRequest(queryParams: ['token' => ''])],
            [$this->createRequest(headers: ['x-istio-jwt-payload' => $this->getValidOriginToken()])],
            [$this->createRequest(headers: ['authorization' => $this->getValidBase64Payload()])],
            [$this->createRequest(headers: ['x-istio-jwt-payload' => $this->getInvalidBase64Payload()])],
            [$this->createRequest(headers: ['authorization' => $this->getInvalidOriginToken()])],
            [$this->createRequest(queryParams: ['token' => $this->getInvalidOriginToken()])],
            [$this->createRequest(headers: ['authorization' => 'Bearer=' . $this->getValidOriginToken()])],
            [$this->createRequest(headers: ['authorization' => 'BearEr ' . $this->getValidOriginToken()])],
        ];
    }

    private function getExtractor(): ExtractorInterface
    {
        return ExtractorFactory::fromExtractors(
            ExtractorFactory::fromBase64Header('valid', 'x-istio-jwt-payload'),
            ExtractorFactory::fromOriginTokenHeader('valid', 'authorization'),
            ExtractorFactory::fromOriginTokenQueryParam('valid', 'token'),
        );
    }

    private function getValidOriginToken(): string
    {
        return sprintf('header.%s.signature', base64_encode(json_encode(['iss' => 'valid'])));
    }

    private function getInvalidOriginToken(): string
    {
        return sprintf('header.%s.signature', base64_encode(json_encode(['iss' => 'invalid'])));
    }

    private function getValidBase64Payload(): string
    {
        return base64_encode(json_encode(['iss' => 'valid']));
    }

    private function getInvalidBase64Payload(): string
    {
        return base64_encode(json_encode(['iss' => 'invalid']));
    }
}
