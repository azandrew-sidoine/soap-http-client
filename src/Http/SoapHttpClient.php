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

namespace Drewlabs\Soap\Http;

use Drewlabs\Async\Awaitable;

use function Drewlabs\Async\Future\async;
use function Drewlabs\Async\Future\await;

use Drewlabs\Soap\Contracts\HttpInterpreterInterface;
use Drewlabs\Soap\Contracts\SoapHttpClientInterface;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class SoapHttpClient implements SoapHttpClientInterface
{
    /**
     * @var Awaitable<HttpInterpreterInterface>
     */
    private $promise;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * Create HTTP SOAP Client instance.
     */
    public function __construct(ClientInterface $client, Awaitable $promise)
    {
        $this->promise = $promise;
        $this->client = $client;
    }

    public function __call($name, $arguments)
    {
        return $this->sendAsync($name, ...$arguments);
    }

    public function send($name, array $arguments, array $options = null, $inputHeaders = null, array &$outputHeaders = null)
    {
        $callPromise = $this->sendAsync($name, $arguments, $options, $inputHeaders, $outputHeaders);

        return $callPromise->wait();
    }

    public function sendAsync($name, array $arguments, array $options = null, $inputHeaders = null, array &$outputHeaders = null)
    {
        return async(function () use ($name, $arguments, $options, $inputHeaders, &$outputHeaders) {
            /**
             * We first initialize the request to null to make if able to clean it up in the finalize block.
             */
            $request = null;
            try {
                /** @var HttpInterpreterInterface $http */
                $http = await($this->promise);
                $request = $http->request($name, $arguments, $options, $inputHeaders);
                yield;
                $response = $this->client->sendRequest($request);

                return $this->interpretResponse($http, $response, $name, $outputHeaders);
            } finally {
                if ($request) {
                    $request->getBody()->close();
                }
            }
        });
    }

    /**
     * Interpret response http response using the http response interpreter.
     *
     * @param mixed $name
     * @param mixed $outputHeaders
     *
     * @throws \SoapFault
     *
     * @return mixed
     */
    private function interpretResponse(HttpInterpreterInterface $http, ResponseInterface $response, $name, &$outputHeaders)
    {
        try {
            return $http->response($response, $name, $outputHeaders);
        } finally {
            $response->getBody()->close();
        }
    }
}
