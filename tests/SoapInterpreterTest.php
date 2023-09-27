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

use Drewlabs\Soap\DefaultSoapRequest;
use Drewlabs\Soap\SoapClient;
use Drewlabs\Soap\SoapInterpreter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SoapInterpreterTest extends TestCase
{
    public function test_soap_interpreter_request_method()
    {
        $action = 'getStockPrice';
        $arguments = ['name' => 'Computer'];
        $options = [];

        $headers = [];
        /**
         * @var MockObject&SoapClient $client
         */
        $client = $this->createMock(SoapClient::class);

        $client->expects($this->once())
            ->method('request')
            ->willReturn('');

        $client->expects($this->once())
            ->method('withRequestClass')
            ->with(DefaultSoapRequest::class)
            ->willReturn($client);

        $client->expects($this->once())
            ->method('withHeaders')
            ->with($headers)
            ->willReturn($client);

        $client
            ->method('__soapCall')
            ->with($action, $arguments, $options)
            ->willReturn('');

        $interpreter = new SoapInterpreter($client);

        // Invoke the interpreter request method
        $interpreter->request($action, $arguments, $options, $headers);

        $this->assertTrue(true);
    }

    public function test_soap_interpreter_response_method()
    {
        $response = '<x:Price>10</x:Price>';
        $action = 'getStockPrice';

        $headers = [];
        /**
         * @var MockObject&SoapClient $client
         */
        $client = $this->createMock(SoapClient::class);

        $client->expects($this->once())
            ->method('response')
            ->willReturn($response);

        $client
            ->method('__soapCall')
            ->with('', [], null, null, $headers)
            ->willReturn('');

        $interpreter = new SoapInterpreter($client);

        $result = $interpreter->response($response, $action, $headers);

        $this->assertSame($response, $result);
    }
}
