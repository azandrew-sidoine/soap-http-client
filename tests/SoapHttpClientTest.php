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

use Drewlabs\Async\Awaitable;

use function Drewlabs\Async\Future\fulfilled;

use Drewlabs\Async\PromiseInterface;
use Drewlabs\Soap\Http\HttpInterpreter;
use Drewlabs\Soap\Http\SoapHttpClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Psr\Http\Message\StreamInterface;

class SoapHttpClientTest extends TestCase
{
    public function test_soap_http_client_send_async()
    {
        /**
         * @var ClientInterface&MockObject
         */
        $client = $this->createMock(ClientInterface::class);

        /**
         * @var Awaitable&MockObject
         */
        $httpInterperter = $this->createMock(Awaitable::class);

        $http = new SoapHttpClient($client, $httpInterperter);

        $promise = $http->sendAsync('getStockPrice', ['name' => 'Computer'], []);

        $this->assertInstanceOf(PromiseInterface::class, $promise);
    }

    public function test_soap_http_client_send()
    {
        /**
         * @var StreamInterface&MockObject
         */
        $responseStream = $this->createMock(StreamInterface::class);
        $responseStream->expects($this->once())->method('close');

        /**
         * @var ResponseInterface&MockObject
         */
        $psrResponse = $this->createMock(ResponseInterface::class);
        $psrResponse->expects($this->once())
            ->method('getBody')
            ->willReturn($responseStream);
        /**
         * @var StreamInterface&MockObject
         */
        $requestStream = $this->createMock(StreamInterface::class);
        $requestStream->expects($this->once())
            ->method('close');
        /**
         * @var RequestInterface&MockObject
         */
        $psrRequest = $this->createMock(RequestInterface::class);
        $psrRequest->expects($this->once())
            ->method('getBody')
            ->willReturn($requestStream);
        /**
         * @var HttpInterpreter&MockObject
         */
        $httpInterpreter = $this->createMock(HttpInterpreter::class);
        $httpInterpreter->expects($this->once())
            ->method('request')
            ->with('getStockPrice', ['name' => 'Computer'], [])
            ->willReturn($psrRequest);
        $httpInterpreter->expects($this->once())
            ->method('response')
            ->with($psrResponse, 'getStockPrice')
            ->willReturn($psrRequest);
        /**
         * @var ClientInterface&MockObject
         */
        $client = $this->createMock(ClientInterface::class);

        $client->method('sendRequest')
            ->with($psrRequest)
            ->willReturn($psrResponse);

        /**
         * @var Awaitable&MockObject
         */
        $httpInterpreterPromise = fulfilled($httpInterpreter);

        $http = new SoapHttpClient($client, $httpInterpreterPromise);

        $promise = $http->send('getStockPrice', ['name' => 'Computer'], []);
    }
}
