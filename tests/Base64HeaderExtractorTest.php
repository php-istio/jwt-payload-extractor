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
use Symfony\Component\HttpFoundation\Request;

class Base64HeaderExtractorTest extends TestCase
{
    use RequestDataProviderTrait;

    public function testInitWithBlankIssuer(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('~can not be blank!~');

        new Base64HeaderExtractor('', 'Authorization');
    }

    /**
     * @dataProvider invalidRequests
     */
    public function testExtractFromInvalidRequests(Request $inHeader)
    {
        $extractor = new Base64HeaderExtractor('valid', 'Authorization');
        $payloadFromHeader = $extractor->extract($inHeader);

        $this->assertNull($payloadFromHeader);
    }

    /**
     * @dataProvider validRequests
     */
    public function testExtractFromValidRequests(Request $inHeader)
    {
        $extractor = new Base64HeaderExtractor('valid', 'Authorization');
        $payloadFromHeader = $extractor->extract($inHeader);

        $this->assertIsArray($payloadFromHeader);
        $this->assertSame('valid', $payloadFromHeader['iss']);
    }

    protected function getValidToken(): string
    {
        return base64_encode(json_encode(['iss' => 'valid']));
    }

    protected function getInvalidToken(): string
    {
        return base64_encode(json_encode(['iss' => 'invalid']));
    }
}
