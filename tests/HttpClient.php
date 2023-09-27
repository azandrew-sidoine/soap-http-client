<?php

declare(strict_types=1);

/*
 * This file is part of the drewlabs namespace.
 *
 * (c) Sidoine Azandrew <azandrewdevelopper@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Drewlabs\Soap\Tests;

use Drewlabs\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClient implements ClientInterface
{
    /**
     * @var array<string,ResponseInterface>
     */
    private $responses = [];

    private $defaultResponse;

    public function __construct(ResponseInterface $response = null)
    {
        $this->defaultResponse = $response ?? new Response('', 200, []);
    }

    /**
     * Set the default response object that is returned if no url matches.
     *
     * @return static
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->defaultResponse = $response;

        return $this;
    }

    /**
     * Set response for a request path.
     *
     * @return static
     */
    public function setResponseFor(string $url, ResponseInterface $response)
    {
        $this->responses[rtrim($url, '/')] = $response;

        return $this;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $url = rtrim($request->getUri()->__toString(), '/');
        if ($response = $this->responses[$url]) {
            return $response;
        }

        return $this->defaultResponse;
    }
}
