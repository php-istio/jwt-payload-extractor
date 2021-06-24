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

interface ExtractorInterface
{
    /*
     * Extract JWT payload from server request.
     */
    public function extract(ServerRequestInterface $request): ?array;
}
