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

use Drewlabs\Soap\Contracts\HttpInterpreterInterface;
use Drewlabs\Soap\Exceptions\SoapRequestException;
use Drewlabs\Soap\Psr7\RequestBuilder as Psr7RequestBuilder;
use Drewlabs\Soap\SoapInterpreter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class HttpInterpreter implements HttpInterpreterInterface
{
    /**
     * @var SoapInterpreter
     */
    private $interpreter;
    /**
     * @var Psr7RequestBuilder
     */
    private $builder;
    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * @return void
     */
    public function __construct(SoapInterpreter $interpreter, Psr7RequestBuilder $builder, StreamFactoryInterface $streamFactory)
    {
        $this->interpreter = $interpreter;
        $this->builder = $builder;
        $this->streamFactory = $streamFactory;
    }

    public function request($name, array $arguments, array $options = null, $inputHeaders = null)
    {
        $soapRequest = $this->interpreter->request($name, $arguments, $options, $inputHeaders);
        if ('1' === (string) $soapRequest->version()) {
            $this->builder->isSOAP1_1();
        } else {
            $this->builder->isSOAP1_2();
        }
        $this->builder->setLocation($soapRequest->location());
        $this->builder->setAction($soapRequest->action());

        $stream = $this->streamFactory->createStream();
        $stream->write($soapRequest->body());
        $stream->rewind();
        $this->builder->setMessage((string) $stream);
        try {
            return $this->builder->createRequest();
        } catch (SoapRequestException $exception) {
            $stream->close();
            throw $exception;
        }
    }

    public function response(ResponseInterface $response, $name, array &$outputHeaders = null)
    {
        return $this->interpreter->response($response->getBody()->__toString(), $name, $outputHeaders);
    }
}
