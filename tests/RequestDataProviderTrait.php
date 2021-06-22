<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\JWTPayloadExtractor\Tests;

use Symfony\Component\HttpFoundation\Request;

trait RequestDataProviderTrait
{
    public function invalidRequests(): array
    {
        return [
            [
                Request::create(''),
                Request::create(''),
            ],
            [
                Request::create('', server: ['HTTP_INVALID_NAME' => $this->getValidToken()]),
                Request::create('', parameters: ['invalid_name' => $this->getValidToken()]),
            ],
            [
                Request::create('', server: ['HTTP_AUTHORIZATION' => '']),
                Request::create('', parameters: ['token' => '']),
            ],
            [
                Request::create('', server: ['HTTP_AUTHORIZATION' => 'invalid header']),
                Request::create('', parameters: ['token' => 'invalid param']),
            ],
            [
                Request::create('', server: ['HTTP_AUTHORIZATION' => $this->getInvalidToken()]),
                Request::create('', parameters: ['token' => $this->getInvalidToken()]),
            ],
        ];
    }

    public function validRequests(): array
    {
        return [
            [
                Request::create('', server: ['HTTP_AUTHORIZATION' => $this->getValidToken()]),
                Request::create('', parameters: ['token' => $this->getValidToken()]),
            ],
        ];
    }

    abstract protected function getValidToken(): string;

    abstract protected function getInvalidToken(): string;
}
