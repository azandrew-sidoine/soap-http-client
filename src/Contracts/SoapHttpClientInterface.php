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

use Drewlabs\Async\Awaitable;
use Drewlabs\Async\PromiseInterface;

/**
 * @psalm-template T
 *
 * @template T
 */
interface SoapHttpClientInterface
{
    /**
     * Magic method to simplify SOAP call. This method is asynchronous.
     *
     * @param string $name      the name of the SOAP function to call
     * @param array  $arguments an array of the arguments to pass to the function
     *
     * @return PromiseInterface<T>&Awaitable
     */
    public function __call($name, $arguments);

    /**
     * Synchronous SOAP call.
     *
     * @param string $name          the name of the SOAP function to call
     * @param array  $arguments     an array of the arguments to pass to the function
     * @param array  $options       An associative array of options to pass to the client.
     *                              The location option is the URL of the remote Web service.
     *                              The uri option is the target namespace of the SOAP service.
     *                              The soapaction option is the action to call.
     * @param mixed  $inputHeaders  an array of headers to be sent along with the SOAP request
     * @param array  $outputHeaders if supplied, this array will be filled with the headers from the SOAP response
     *
     * @return T the eventual value of promise returned from callAsync
     */
    public function send($name, array $arguments, array $options = null, $inputHeaders = null, array &$outputHeaders = null);

    /**
     * Asynchronous SOAP call.
     *
     * @param string $name          the name of the SOAP function to call
     * @param array  $arguments     an array of the arguments to pass to the function
     * @param array  $options       An associative array of options to pass to the client.
     *                              The location option is the URL of the remote Web service.
     *                              The uri option is the target namespace of the SOAP service.
     *                              The soapaction option is the action to call.
     * @param mixed  $inputHeaders  an array of headers to be sent along with the SOAP request
     * @param array  $outputHeaders if supplied, this array will be filled with the headers from the SOAP response
     *
     * @return PromiseInterface<T>&Awaitable A promise object
     */
    public function sendAsync($name, array $arguments, array $options = null, $inputHeaders = null, array &$outputHeaders = null);
}
