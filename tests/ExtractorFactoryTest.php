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
use Istio\JWTPayloadExtractor\CompositeExtractor;
use Istio\JWTPayloadExtractor\ExtractorFactory;
use Istio\JWTPayloadExtractor\OriginTokenExtractor;
use PHPUnit\Framework\TestCase;

class ExtractorFactoryTest extends TestCase
{
    public function testMatchInstanceOf(): void
    {
        $this->assertInstanceOf(
            OriginTokenExtractor::class,
            ExtractorFactory::fromOriginTokenHeader('valid', 'Authorization')
        );

        $this->assertInstanceOf(
            OriginTokenExtractor::class,
            ExtractorFactory::fromOriginTokenQueryParam('valid', 'token')
        );

        $this->assertInstanceOf(
            Base64HeaderExtractor::class,
            ExtractorFactory::fromBase64Header('valid', 'Authorization')
        );

        $this->assertInstanceOf(
            CompositeExtractor::class,
            ExtractorFactory::fromExtractors()
        );
    }
}
