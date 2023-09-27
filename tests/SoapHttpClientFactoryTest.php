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

use Drewlabs\Soap\Contracts\SoapHttpClientInterface;
use Drewlabs\Soap\Http\SoapHttpClientFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class SoapHttpClientFactoryTest extends TestCase
{
    public function test_create_soap_http_client()
    {
        $client = $this->createMock(ClientInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $factory = new SoapHttpClientFactory();

        $soapHttpClient = $factory->create($client, $streamFactory, $requestFactory, realpath(__DIR__.'/service.wsdl'), []);

        $this->assertInstanceOf(SoapHttpClientInterface::class, $soapHttpClient);
    }
}
