<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\JWTPayloadExtractor;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractExtractor implements ExtractorInterface
{
    private string $requestBag;

    private string $itemName;

    private string $issuer;

    public function __construct(
        string $issuer,
        string $requestBag,
        string $itemName
    ) {
        if ('' === $issuer) {
            throw new \LogicException('Issuer can not be blank!');
        }

        $this->issuer = $issuer;
        $this->requestBag = $requestBag;
        $this->itemName = $itemName;
    }

    final public function extract(Request $request): ?array
    {
        /** @var InputBag|HeaderBag $bag */
        $bag = $request->{$this->requestBag};
        $value = $bag->get($this->itemName);

        if (false !== is_string($value)) {
            return null;
        }

        $payload = $this->extractFromValue($value);

        if (null === $payload || $this->issuer !== ($payload['iss'] ?? null)) {
            return null;
        }

        return $payload;
    }

    abstract protected function extractFromValue(string $value): ?array;

    final protected function extractFromBase64EncodedPayload(string $encodedPayload): ?array
    {
        $jsonPayload = base64_decode($encodedPayload, true);

        if (false === $jsonPayload) {
            return null;
        }

        $payload = json_decode($jsonPayload, true);

        if (null === $payload) {
            return null;
        }

        return $payload;
    }
}
