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

use Drewlabs\Soap\Contracts\RequestInterface as ContractsRequestInterface;
use Drewlabs\Soap\DefaultSoapRequest;
use Drewlabs\Soap\Http\HttpInterpreter;
use Drewlabs\Soap\Http\RequestBuilder;
use Drewlabs\Soap\SoapInterpreter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class HttpInterpreterTest extends TestCase
{
    public function test_http_interpreter_request()
    {
        $action = 'getStockPrice';
        $arguments = ['name' => 'Name'];
        $options = [];
        $headers = [];
        $location = 'http://127.0.0.1/api/v1/stock-price';

        $request = new DefaultSoapRequest('', $location, 'getStockPrice', \SOAP_1_1);

        $psr7Request = $this->createMock(RequestInterface::class);

        /**
         * @var MockObject&SoapInterpreter $soapInterpreter
         */
        $soapInterpreter = $this->createMock(SoapInterpreter::class);

        $soapInterpreter->expects($this->exactly(1))
            ->method('request')
            ->with($action, $arguments, $options, $headers)
            ->willReturn($request);

        /**
         * @var RequestBuilder&MockObject $builder
         */
        $builder = $this->createMock(RequestBuilder::class);

        $builder->expects($this->exactly(1))
            ->method('isSOAP1_1')
            ->willReturn($builder);

        $builder->expects($this->exactly(1))
            ->method('setLocation')
            ->with($location)
            ->willReturn($builder);

        $builder->expects($this->exactly(1))
            ->method('setAction')
            ->with($action)
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('setMessage')
            ->with($request->body())
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('createRequest')
            ->willReturn($psr7Request);

        // Stream factory mock
        /**
         * @var MockObject $stream
         */
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->once())
            ->method('write')
            ->with($request->body())
            ->willReturn(strlen($request->body()));
        $stream->method('rewind');
        $stream->expects($this->once())
            ->method('__toString')
            // TODO : Add SOAP string body
            ->willReturn($request->body());
        /**
         * @var StreamFactoryInterface&MockObject $streamFactory
         */
        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $streamFactory->expects($this->once())
            ->method('createStream')
            ->willReturn($stream);

        $httpInterpreter = new HttpInterpreter($soapInterpreter, $builder, $streamFactory);

        $result = $httpInterpreter->request($action, $arguments, $options, $headers);

        $this->assertSame($psr7Request, $result);
    }

    public function test_http_interpreter_response()
    {
        $action = 'getStockPrice';
        $arguments = ['name' => 'Name'];
        $options = [];
        $headers = [];
        $location = 'http://127.0.0.1/api/v1/stock-price';

        $request = new DefaultSoapRequest('', $location, 'getStockPrice', \SOAP_1_1);

        /**
         * @var MockObject&StreamInterface $psrStream
         */
        $psrStream = $this->createMock(StreamInterface::class);
        $psrStream->expects($this->once())
            ->method('__toString')
            ->willReturn('My Response');

        /**
         * @var MockObject&ResponseInterface $psrResponse
         */
        $psrResponse = $this->createMock(ResponseInterface::class);
        $psrResponse->expects($this->once())
            ->method('getBody')
            ->willReturn($psrStream);

        /**
         * @var MockObject&SoapInterpreter $soapInterpreter
         */
        $soapInterpreter = $this->createMock(SoapInterpreter::class);

        $soapInterpreter->expects($this->exactly(1))
            ->method('response')
            ->with('My Response', $action, $headers)
            ->willReturn($request);

        /**
         * @var RequestBuilder&MockObject $builder
         */
        $builder = $this->createMock(RequestBuilder::class);

        // Stream factory mock
        /**
         * @var StreamFactoryInterface&MockObject $streamFactory
         */
        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $httpInterpreter = new HttpInterpreter($soapInterpreter, $builder, $streamFactory);

        $response = $httpInterpreter->response($psrResponse, $action, $headers);

        $this->assertInstanceOf(ContractsRequestInterface::class, $response);
    }
}
