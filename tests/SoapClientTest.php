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

use Drewlabs\Soap\Contracts\RequestInterface;
use Drewlabs\Soap\Exceptions\SoapRequestException;
use Drewlabs\Soap\SoapClient;
use PHPUnit\Framework\TestCase;

class SoapClientTest extends TestCase
{
    public function test_client___do_request()
    {
        $wsdlPath = realpath(__DIR__.'/service.wsdl');
        $client = new SoapClient($wsdlPath);

        $result = $client->__doRequest('<m:Price>10</m:Price>', $wsdlPath, '', '1.1');

        $this->assertSame('', $result);
    }

    public function test_soap_client_request_request_an_soap_request_interface()
    {
        $wsdlPath = realpath(__DIR__.'/service.wsdl');
        $client = new SoapClient($wsdlPath);

        $request = $client->request('getStockPrice', ['name' => 'Computer']);

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertSame('http://127.0.0.1/api/v1/stock-price', $request->location());
        $this->assertSame(1, $request->version());
        $this->assertSame('urn:xmethods-delayed-quotes#getStockPrice', $request->action());
        $this->assertFalse($request->isOneWay());
    }

    public function test_soap_client_request_throws_an_soap_request_exception_if_method_does_not_exist()
    {
        $wsdlPath = realpath(__DIR__.'/service.wsdl');
        $client = new SoapClient($wsdlPath);

        $this->expectException(SoapRequestException::class);
        $client->request('fakeRequest', ['name' => 'Computer']);
    }

    // public function test_soap_client_response_mock_call()
    // {
    //     $headers = [];
    //     /**
    //      * @var MockObject&SoapClient $client
    //      */
    //     $client = $this->createMock(SoapClient::class);

    //     $client->method('response')
    //             ->willReturn('<x:Price>10</x:Price>');

    //     $client->expects($this->once())
    //             ->method('__soapCall')
    //             ->with('getStockPrice', [], null, null, $headers)
    //             ->willReturn('');

    //     $response =  $client->response('<x:Price>10</x:Price>', 'getStockPrice', $headers);

    //     $this->assertEquals('<x:Price>10</x:Price>', $response);
    // }
}
