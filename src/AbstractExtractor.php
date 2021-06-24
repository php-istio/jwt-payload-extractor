<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\JWTPayloadExtractor;

use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractExtractor implements ExtractorInterface
{
    private string $in;

    private string $item;

    private string $issuer;

    public function __construct(
        string $issuer,
        string $in,
        string $item
    ) {
        if ('' === $issuer) {
            throw new \LogicException('Issuer can not be blank!');
        }

        if (ExtractorInterface::IN_HEADER !== $in && ExtractorInterface::IN_QUERY_PARAM !== $in) {
            throw new \LogicException(
                sprintf(
                    'Origin token must in: `%s` or `%s`, can not in: `%s`',
                    ExtractorInterface::IN_HEADER,
                    ExtractorInterface::IN_QUERY_PARAM,
                    $in
                )
            );
        }

        $this->issuer = $issuer;
        $this->in = $in;
        $this->item = $item;
    }

    final public function extract(ServerRequestInterface $request): ?array
    {
        $value = match ($this->in) {
            ExtractorInterface::IN_HEADER => $request->getHeader($this->item)[0] ?? null,
            ExtractorInterface::IN_QUERY_PARAM => $request->getQueryParams()[$this->item] ?? null
        };

        if (false === is_string($value)) {
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
