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
use Symfony\Component\HttpFoundation\Request;

class CompositeExtractorTest extends TestCase
{
    /**
     * @dataProvider validRequests
     */
    public function testExtractFromValidRequestsWithEmptyExtractors(Request $request): void
    {
        $extractor = ExtractorFactory::fromExtractors();
        $payload = $extractor->extract($request);

        $this->assertNull($payload);
    }

    /**
     * @dataProvider validRequests
     */
    public function testExtractFromValidRequests(Request $request): void
    {
        $extractor = $this->getExtractor();
        $payload = $extractor->extract($request);

        $this->assertIsArray($payload);
        $this->assertSame('valid', $payload['iss']);
    }

    /**
     * @dataProvider invalidRequests
     */
    public function testExtractFromInvalidRequests(Request $request): void
    {
        $extractor = $this->getExtractor();
        $payload = $extractor->extract($request);

        $this->assertNull($payload);
    }

    public function validRequests(): array
    {
        return [
            [Request::create('', server: ['HTTP_X_JWT_PAYLOAD' => $this->getValidBase64Payload()])],
            [Request::create('', server: ['HTTP_AUTHORIZATION' => $this->getValidOriginToken()])],
            [Request::create('', parameters: ['token' => $this->getValidOriginToken()])],
        ];
    }

    public function invalidRequests(): array
    {
        return [
            [Request::create('')],
            [Request::create('', server: ['HTTP_X_JWT_PAYLOAD' => ''])],
            [Request::create('', server: ['HTTP_AUTHORIZATION' => ''])],
            [Request::create('', parameters: ['token' => ''])],
            [Request::create('', server: ['HTTP_X_JWT_PAYLOAD' => $this->getValidOriginToken()])],
            [Request::create('', server: ['HTTP_AUTHORIZATION' => $this->getValidBase64Payload()])],
            [Request::create('', server: ['HTTP_X_JWT_PAYLOAD' => $this->getInvalidBase64Payload()])],
            [Request::create('', server: ['HTTP_AUTHORIZATION' => $this->getInvalidOriginToken()])],
            [Request::create('', parameters: ['token' => $this->getInvalidOriginToken()])],
        ];
    }

    private function getExtractor(): ExtractorInterface
    {
        return ExtractorFactory::fromExtractors(
            ExtractorFactory::fromBase64Header('valid', 'X-JWT-Payload'),
            ExtractorFactory::fromOriginTokenHeader('valid', 'Authorization'),
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
