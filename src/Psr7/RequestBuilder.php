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

namespace Drewlabs\Soap\Psr7;

use Drewlabs\Soap\Exceptions\SoapRequestException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @see https://github.com/meng-tian/soap-http-binding
 */
class RequestBuilder
{
    /**
     * @var SOAP version 1.1
     */
    public const SOAP1_1 = '1.1';

    /**
     * @var SOAP version 1.2
     */
    public const SOAP1_2 = '1.2';

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $action;

    /**
     * @var StreamInterface
     */
    private $message;

    /**
     * @var bool
     */
    private $hasMessage;

    /**
     * @var string
     */
    private $method = 'POST';

    /**
     * @var RequestFactoryInterface
     */
    private $messageFactory;

    /**
     * @var StreamFactory
     */
    private $streamFactory;

    /**
     * Creates a request builder instance.
     */
    public function __construct(RequestFactoryInterface $messageFactory, StreamFactoryInterface $streamFactory)
    {
        $this->messageFactory = $messageFactory;
        $this->streamFactory = $streamFactory;
        $this->version = self::SOAP1_1;
        $this->action = '';
        $this->hasMessage = false;
    }

    /**
     * @throws SoapRequestException
     */
    public function createRequest(): RequestInterface
    {
        $this->validate();

        try {
            $request = $this->messageFactory
                ->createRequest($this->method, $this->location)
                ->withBody($this->prepareMessage());

            foreach ($this->prepareHeaders() as $name => $value) {
                $request = $request->withHeader($name, $value);
            }
        } catch (\Throwable $e) {
            throw new SoapRequestException($e->getMessage(), $e->getCode(), $e);
        }

        return $request;
    }

    public function setLocation(string $location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Set the flag to create SOAP1_1 request.
     */
    public function isSOAP1_1()
    {
        $this->version = self::SOAP1_1;

        return $this;
    }

    /**
     * Set the flag to create SOAP1_2 request.
     */
    public function isSOAP1_2()
    {
        $this->version = self::SOAP1_2;

        return $this;
    }

    public function setAction(string $soapAction)
    {
        $this->action = $soapAction;

        return $this;
    }

    public function setMessage(string $content)
    {
        $this->message = $this->streamFactory->createStream($content);
        $this->hasMessage = true;

        return $this;
    }

    public function setHttpMethod(string $method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @throws SoapRequestException
     *
     * @return void
     */
    private function validate()
    {
        if (!$this->location) {
            throw new SoapRequestException('There is no endpoint specified.');
        }

        if (!$this->hasMessage && $this->isPostRequest()) {
            throw new SoapRequestException('There is no SOAP message specified.');
        }

        /*
         * SOAP 1.1 only defines HTTP binding with POST method.
         * @link https://www.w3.org/TR/2000/NOTE-SOAP-20000508/#_Toc478383527
         */
        if (self::SOAP1_1 === $this->version && !$this->isPostRequest()) {
            throw new SoapRequestException('You cannot use SOAP 1.1. with non POST HTTP method');
        }

        /*
         * SOAP 1.2 only defines HTTP binding with POST and GET methods.
         * @link https://www.w3.org/TR/2007/REC-soap12-part0-20070427/#L10309
         */
        if (self::SOAP1_2 === $this->version && !\in_array($this->method, ['GET', 'POST'], true)) {
            throw new SoapRequestException('Invalid SOAP method specified for SOAP 1.2. Expeted: GET or POST.');
        }
    }

    private function prepareHeaders(): array
    {
        if (self::SOAP1_1 === $this->version) {
            return $this->prepareSoap1_1Headers();
        }

        return $this->prepareSoap1_2Headers();
    }

    /**
     * @see https://www.w3.org/TR/2000/NOTE-SOAP-20000508/#_Toc478383526
     */
    private function prepareSoap1_1Headers(): array
    {
        $headers = [];
        $headers['Content-Length'] = (string) $this->message->getSize();
        $headers['SOAPAction'] = $this->action;
        $headers['Content-Type'] = 'text/xml; charset="utf-8"';

        return $headers;
    }

    /**
     * SOAPAction header is removed in SOAP 1.2 and now expressed as a value of
     * an (optional) "action" parameter of the "application/soap+xml" media type.
     *
     * @see https://www.w3.org/TR/soap12-part0/#L4697
     */
    private function prepareSoap1_2Headers(): array
    {
        $headers = [];
        if ('POST' !== $this->method) {
            $headers['Accept'] = 'application/soap+xml';

            return $headers;
        }
        $headers['Content-Length'] = (string) $this->message->getSize();
        $headers['Content-Type'] = 'application/soap+xml; charset="utf-8"; action="'.$this->action.'"';

        return $headers;
    }

    private function prepareMessage(): StreamInterface
    {
        return $this->isPostRequest() ? $this->message : $this->streamFactory->createStream('');
    }

    private function isPostRequest()
    {
        return 'POST' === strtoupper($this->method);
    }
}
