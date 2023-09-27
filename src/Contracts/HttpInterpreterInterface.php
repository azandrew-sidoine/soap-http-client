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

namespace Drewlabs\Soap\Contracts;

use Drewlabs\Soap\Exceptions\SoapRequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpInterpreterInterface
{
    /**
     * Embed SOAP messages in PSR-7 HTTP Requests.
     *
     * @param string $name         the name of the SOAP function to bind
     * @param array  $arguments    an array of the arguments to the SOAP function
     * @param array  $options      An associative array of options.
     *                             The location option is the URL of the remote Web service.
     *                             The uri option is the target namespace of the SOAP service.
     *                             The soapaction option is the action to call.
     * @param mixed  $inputHeaders an array of headers to be bound along with the SOAP request
     *
     * @throws SoapRequestException if SOAP HTTP binding failed using the given parameters
     *
     * @return RequestInterface
     */
    public function request($name, array $arguments, array $options = null, $inputHeaders = null);

    /**
     * Retrieve SOAP messages from PSR-7 HTTP responses and interpret messages to PHP values.
     *
     * @param string $name          the name of the SOAP function to unbind
     * @param array  $outputHeaders if supplied, this array will be filled with the headers from
     *                              the SOAP response
     *
     * @throws \SoapFault if the SOAP message contains SOAP faults
     *
     * @return mixed
     */
    public function response(ResponseInterface $response, $name, array &$outputHeaders = null);
}
