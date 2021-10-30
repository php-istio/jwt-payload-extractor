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
    public const IN_HEADER = 'header';

    public const IN_QUERY_PARAM = 'query_param';

    private string $in;

    private string $item;

    private string $issuer;

    private ?string $prefix;

    public function __construct(
        string $issuer,
        string $in,
        string $item,
        string $prefix = null
    ) {
        if ('' === $issuer) {
            throw new \LogicException('Issuer can not be blank!');
        }

        if (self::IN_HEADER !== $in && self::IN_QUERY_PARAM !== $in) {
            throw new \LogicException(sprintf('Item must in: `%s` or `%s`, can not in: `%s`', self::IN_HEADER, self::IN_QUERY_PARAM, $in));
        }

        $this->issuer = $issuer;
        $this->in = $in;
        $this->item = $item;
        $this->prefix = $prefix;
    }

    final public function extract(ServerRequestInterface $request): ?array
    {
        $value = match ($this->in) {
            self::IN_HEADER => $request->getHeader($this->item)[0] ?? null,
            self::IN_QUERY_PARAM => $request->getQueryParams()[$this->item] ?? null
        };

        if (false === is_string($value)) {
            return null;
        }

        if (null !== $this->prefix) {
            if (!str_starts_with($value, $this->prefix)) {
                return null;
            }

            $value = substr($value, strlen($this->prefix));
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
