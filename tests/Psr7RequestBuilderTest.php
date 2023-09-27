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

use Drewlabs\Psr7\Factories\RequestFactory;
use Drewlabs\Psr7Stream\StreamFactory;
use Drewlabs\Soap\Exceptions\SoapRequestException;
use Drewlabs\Soap\Psr7\RequestBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class Psr7RequestBuilderTest extends TestCase
{
    public function test_request_builder_create_request_throws_exception_if_location_is_not_set()
    {
        $this->expectException(SoapRequestException::class);
        $this->expectExceptionMessage('There is no endpoint specified.');
        $builder = new RequestBuilder(new RequestFactory(), new StreamFactory());
        $builder->createRequest();
    }

    public function test_request_builder_create_request_throws_exception_if_http_method_is_post_and_message_is_not_set()
    {
        $this->expectException(SoapRequestException::class);
        $this->expectExceptionMessage('There is no SOAP message specified.');
        $builder = new RequestBuilder(new RequestFactory(), new StreamFactory());
        $builder->setLocation('http://www.dneonline.com/calculator.asmx?wsdl')
            ->setHttpMethod('POST')
            ->createRequest();
    }

    public function test_request_builder_create_request_throws_exception_if_request_is_soap1_1_but_method_is_not_post()
    {
        $this->expectException(SoapRequestException::class);
        $this->expectExceptionMessage('You cannot use SOAP 1.1. with non POST HTTP method');
        $builder = new RequestBuilder(new RequestFactory(), new StreamFactory());
        $builder->setLocation('http://www.dneonline.com/calculator.asmx?wsdl')
            ->setHttpMethod('GET')
            ->setMessage('My Http Message')
            ->createRequest();
    }

    public function test_request_builder_create_request_throws_exception_if_not_get_or_post()
    {
        $this->expectException(SoapRequestException::class);
        $this->expectExceptionMessage('Invalid SOAP method specified for SOAP 1.2. Expeted: GET or POST.');
        $builder = new RequestBuilder(new RequestFactory(), new StreamFactory());
        $builder->setLocation('http://www.dneonline.com/calculator.asmx?wsdl')
            ->setHttpMethod('PUT')
            ->isSOAP1_2()
            ->setMessage('<m:Value>2</m:Value>')
            ->createRequest();
    }

    public function test_request_builder_creates_psr_request_instance_if_validation_passes()
    {
        $builder = new RequestBuilder(new RequestFactory(), new StreamFactory());
        $request = $builder->setLocation('http://www.dneonline.com/calculator.asmx?wsdl')
            ->setHttpMethod('POST')
            ->isSOAP1_2()
            ->setMessage('<m:Value>2</m:Value>')
            ->createRequest();

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertSame('<m:Value>2</m:Value>', $request->getBody()->__toString());
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('http://www.dneonline.com/calculator.asmx?wsdl', $request->getUri()->__toString());
        $this->assertTrue(str_contains(array_pop($request->getHeader('content-type')), 'application/soap+xml'));
    }
}
