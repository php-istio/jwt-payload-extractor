<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\JWTPayloadExtractor\Tests;

trait RequestDataProviderTrait
{
    use RequestCreatorTrait;

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
                $this->createRequest(headers: ['authorization' => 'invalid header']),
                $this->createRequest(queryParams: ['token' => 'invalid query params']),
            ],
            [
                $this->createRequest(headers: ['authorization' => $this->getInvalidToken()]),
                $this->createRequest(queryParams: ['token' => $this->getInvalidToken()]),
            ],
        ];
    }

    public function validRequests(): array
    {
        return [
            [
                $this->createRequest(headers: ['authorization' => $this->getValidToken()]),
                $this->createRequest(queryParams: ['token' => $this->getValidToken()]),
            ],
        ];
    }

    abstract protected function getValidToken(): string;

    abstract protected function getInvalidToken(): string;
}
