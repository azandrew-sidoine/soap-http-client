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

namespace Drewlabs\Soap;

use Drewlabs\Soap\Contracts\RequestInterface;

class SoapInterpreter
{
    /**
     * @var SoapClient
     */
    private $client;

    /**
     * The request class that is binded to the SoapClient
     * That request class can be used to modify the actual request string.
     *
     * @var string
     */
    private $requestClass;

    public function __construct(SoapClient $client, string $requestClass = null)
    {
        $this->client = $client;
        $requestClass = $requestClass ?? DefaultSoapRequest::class;
        $this->bindRequest($requestClass);
    }

    public function bindRequest(string $class)
    {
        $this->requestClass = $class;

        return $this;
    }

    /**
     * Interpret the given method and arguments to a SOAP request message.
     *
     * @param string $action  the name of the SOAP function to interpret
     * @param array  $args    an array of the arguments to $func
     * @param array  $options An associative array of options.
     *                        The location option is the URL of the remote Web service.
     *                        The uri option is the target namespace of the SOAP service.
     *                        The soapaction option is the action to call.
     * @param mixed  $headers an array of headers to be interpreted along with the SOAP request
     *
     * @return RequestInterface
     */
    public function request($action, array $args = [], array $options = null, $headers = null)
    {
        if (null !== $this->requestClass) {
            $this->client = $this->client->withRequestClass($this->requestClass);
        }

        return $this->client->withHeaders($headers)->request($action, $args, $options);
    }

    /**
     * Interpret a SOAP response message to PHP values.
     *
     * @param string $response       the SOAP response message
     * @param string $action         the name of the SOAP function to interpret
     * @param array  $output_headers if supplied, this array will be filled with the headers from the SOAP response
     *
     * @throws \SoapFault
     *
     * @return mixed
     */
    public function response($response, $action, array &$output_headers = null)
    {
        return $this->client->response($response, $action, $output_headers);
    }
}
