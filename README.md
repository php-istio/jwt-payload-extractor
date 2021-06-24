# JWT Payload Extractor

![example workflow](https://github.com/php-istio/jwt-payload-extractor/actions/workflows/unit-tests.yml/badge.svg)
![example workflow](https://github.com/php-istio/jwt-payload-extractor/actions/workflows/coding-standards.yml/badge.svg)
[![codecov](https://codecov.io/gh/php-istio/jwt-payload-extractor/branch/main/graph/badge.svg?token=I2ZACWOYHM)](https://codecov.io/gh/php-istio/jwt-payload-extractor)

## About

This library help to extract trusted JWT payload from request forwarded by Istio Envoy proxy. It's based
on [PSR-7 Server Request Message](https://www.php-fig.org/psr/psr-7/) ensures interoperability with other packages and
frameworks.

![UML](assets/request.png)

## Requirements

PHP versions:

+ PHP 8.0

## Installation

First install this library:

```shell
composer require php-istio/jwt-payload-extractor
```

And choice one of PSR-7 implementation package (ex: [nyholm/psr7-server](https://github.com/Nyholm/psr7-server/)):

```shell
composer require nyholm/psr7 nyholm/psr7-server
```

## Usage

Istio CRD [JWTRules](https://istio.io/latest/docs/reference/config/security/jwt/#JWTRule) support forward origin
token (`forwardOriginalToken` option), or just only base64 payload via specify header name
(`outputPayloadToHeader` option), depend on your strategy you need to select method to extract your trusted JWT payload from forwarded request:

+ Extract from origin token in header:

```php
<?php
$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();

$creator = new \Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);

$serverRequest = $creator->fromGlobals();
$extractor = \Istio\JWTPayloadExtractor\ExtractorFactory::fromOriginTokenHeader('issuer.example', 'authorization');
$payload = $extractor->extract($serverRequest);

if(null !== $payload) {
    var_dump($payload);
}
```

+ Extract origin token in query param:

```php
<?php
//......
$extractor = \Istio\JWTPayloadExtractor\ExtractorFactory::fromOriginTokenQueryParam('issuer.example', 'token');
$payload = $extractor->extract($serverRequest);
//......
```

+ Extract base64 payload header:

```php
<?php
//......
$extractor = \Istio\JWTPayloadExtractor\ExtractorFactory::fromBase64Header('issuer.example', 'x-istio-jwt-payload');
$payload = $extractor->extract($serverRequest);
//......
```

+ In case your application have many JWT issuers, or many extraction strategies:

```php
<?php
//......
$extractor = \Istio\JWTPayloadExtractor\ExtractorFactory::fromExtractors(
    \Istio\JWTPayloadExtractor\ExtractorFactory::fromBase64Header('issuer1.example', 'x-istio-jwt-payload'),
    \Istio\JWTPayloadExtractor\ExtractorFactory::fromOriginTokenQueryParam('issuer1.example', 'token'),
    \Istio\JWTPayloadExtractor\ExtractorFactory::fromOriginTokenHeader('issuer2.example', 'authorization'),
    \Istio\JWTPayloadExtractor\ExtractorFactory::fromOriginTokenQueryParam('issuer3.example', 'token'),
);
$payload = $extractor->extract($serverRequest);
//......
```

## Testing

This library uses [PHPUnit](https://phpunit.de) for unit tests:

```shell
vendor/bin/phpunit
```

## Credits

+ [Minh Vuong](https://github.com/vuongxuongminh)