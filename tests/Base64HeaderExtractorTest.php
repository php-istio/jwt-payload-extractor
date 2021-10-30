<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\JWTPayloadExtractor\Tests;

use Istio\JWTPayloadExtractor\Base64HeaderExtractor;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class Base64HeaderExtractorTest extends TestCase
{
    use RequestCreatorTrait;

    public function testInitWithBlankIssuer(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('~can not be blank!~');

        new Base64HeaderExtractor('', 'x-istio-jwt-payload');
    }

    /**
     * @dataProvider invalidRequests
     */
    public function testExtractFromInvalidRequests(ServerRequestInterface $inHeader)
    {
        $extractor = new Base64HeaderExtractor('valid', 'x-istio-jwt-payload');
        $payloadFromHeader = $extractor->extract($inHeader);

        $this->assertNull($payloadFromHeader);
    }

    /**
     * @dataProvider validRequests
     */
    public function testExtractFromValidRequests(ServerRequestInterface $inHeader)
    {
        $extractor = new Base64HeaderExtractor('valid', 'x-istio-jwt-payload');
        $payloadFromHeader = $extractor->extract($inHeader);

        $this->assertIsArray($payloadFromHeader);
        $this->assertSame('valid', $payloadFromHeader['iss']);
    }

    public function invalidRequests(): array
    {
        return [
            [
                $this->createRequest(),
            ],
            [
                $this->createRequest(headers: ['invalid_name' => '']),
            ],
            [
                $this->createRequest(headers: ['x-istio-jwt-payload' => 'invalid header']),
            ],
            [
                $this->createRequest(headers: ['x-istio-jwt-payload' => 'Bearer ' . base64_encode(json_encode(['iss' => 'invalid']))]),
            ],
        ];
    }

    public function validRequests(): array
    {
        return [
            [
                $this->createRequest(headers: ['x-istio-jwt-payload' => base64_encode(json_encode(['iss' => 'valid']))]),
            ],
        ];
    }
}
