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

use function Drewlabs\Async\Future\async;
use function Drewlabs\Async\Future\fulfilled;

use Drewlabs\Soap\Contracts\SoapHttpClientInterface as SoapClientInterface;
use Drewlabs\Soap\SoapClient;
use Drewlabs\Soap\SoapInterpreter;
use Psr\Http\Client\ClientInterface;

use Psr\Http\Message\RequestFactoryInterface;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriInterface;

class SoapHttpClientFactory
{
    /**
     * Create an instance of {SoapHttpClientInterface}.
     *
     * This method will load WSDL asynchronously if the given WSDL URI is a HTTP URL.
     *
     * @param ClientInterface         $client         a Guzzle HTTP client
     * @param StreamFactoryInterface  $streamFactory  a PSR-17 stream factory
     * @param RequestFactoryInterface $requestFactory a PSR-17 request factory
     * @param array                   $options        Supported options: location, uri, style, use, soap_version, encoding,
     *                                                exceptions, classmap, typemap, and feature. HTTP related options should
     *                                                be configured against $client, e.g., authentication, proxy, user agent,
     *                                                and connection timeout etc.
     *
     * @return SoapClientInterface
     */
    public function create(
        ClientInterface $client,
        StreamFactoryInterface $streamFactory,
        RequestFactoryInterface $requestFactory,
        string $wsdl,
        array $options = [],
        string $soapRequestClass = null
    ) {
        $builder = new RequestBuilder($requestFactory, $streamFactory);
        $promise = $this->isValidHttpURI($wsdl) ? async(static function () use ($client, $wsdl, $streamFactory, $options, $builder, $soapRequestClass, $requestFactory) {
            // yield the start of an IO operation
            yield;
            $response = $client->sendRequest($requestFactory->createRequest('GET', $wsdl));
            $interpreter = new SoapInterpreter(new SoapClient('data://text/plain;base64,'.base64_encode($response->getBody()->__toString()), $options), $soapRequestClass);

            return new HttpInterpreter($interpreter, $builder, $streamFactory);
        }) : fulfilled(new HttpInterpreter(new SoapInterpreter(new SoapClient($wsdl, $options), $soapRequestClass), $builder, $streamFactory));

        // Returns an SOAP Http Client instance
        return new SoapHttpClient($client, $promise);
    }

    /**
     * Checks if a url is a valid resource url.
     *
     * @param string|UriInterface $url
     *
     * @return bool
     */
    private function isValid($url)
    {
        if ($url instanceof UriInterface) {
            $url = (string) $url;
        }

        return false !== filter_var($url, \FILTER_VALIDATE_URL);
    }

    /**
     * Checks if the $url is a valid HTTP resource url.
     *
     * @param string $url
     *
     * @return bool
     */
    private function isValidHttpURI($url)
    {
        $url = \is_string($url) ? $url : (string) $url;

        return $this->isValid($url) && \in_array(parse_url($url, \PHP_URL_SCHEME), ['http', 'https'], true);
    }
}
