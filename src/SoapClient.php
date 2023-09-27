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
use Drewlabs\Soap\Exceptions\SoapRequestException;
use SoapClient as GlobalSoapClient;

/**
 * SPL SOAP client extension class.
 */
class SoapClient extends GlobalSoapClient
{
    private const UNUSED_OPTIONS = [
        'login',
        'password',
        'proxy_host',
        'proxy_port',
        'proxy_login',
        'proxy_password',
        'local_cert',
        'passphrase',
        'authentication',
        'compression',
        'trace',
        'connection_timeout',
        'user_agent',
        'stream_context',
        'keep_alive',
        'ssl_method',
    ];

    /**
     * @var \SoapHeader
     */
    private $headers;

    /**
     * @var string
     */
    private $requestClass;

    /**
     * @var RequestInterface
     */
    private $lastRequest;

    /**
     * @var mixed
     */
    private $lastResponse;

    /**
     * Creates new soap client instance.
     *
     * @return void
     */
    public function __construct(string $wsdl, array $options = [])
    {
        foreach (self::UNUSED_OPTIONS as $value) {
            // code...
            if (\array_key_exists($value, $options)) {
                unset($options[$value]);
            }
        }
        parent::__construct($wsdl, $options);
    }

    /**
     * Perform a request to a SOAP service.
     *
     * @param string $request
     * @param string $location
     * @param string $action_uri
     * @param int    $version
     *
     * @return ?string
     */
    public function __doRequest($request, $location, $action_uri, $version, $one_way = null)
    {
        if (null !== $this->lastResponse) {
            return $this->lastResponse;
        }
        $this->requestClass = $this->requestClass ?? DefaultSoapRequest::class;
        if ((null !== $this->requestClass) && class_exists($this->requestClass)) {
            $clazz = $this->requestClass;
            $soapRequest = new $clazz();
            $this->lastRequest = $soapRequest->setBody($request)
                ->setLocation($location)
                ->setAction($action_uri)
                ->setVersion($version)
                ->setOneWay((bool) $one_way);
        }

        return '';
    }

    /**
     * Undocumented function.
     *
     * @param array|\SoapHeader $headers
     *
     * @return self
     */
    public function withHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    public function withRequestClass(string $clazz)
    {
        $this->requestClass = $clazz;

        return $this;
    }

    /**
     * Call SOAP function.
     *
     * @throws \Exception
     *
     * @return RequestInterface
     */
    public function request(string $action, array $args, array $options = null)
    {
        $this->__setSoapHeaders($this->headers);
        try {
            // __soapCall will invoke the __doRequest() action and set the lastRequest property
            // which is the request interface object
            $this->__soapCall($action, $args, $options);

            // Return the last constructed request
            return $this->lastRequest;
        } catch (\SoapFault $th) {
            throw new SoapRequestException($th->getMessage(), 500, $th);
        }
    }

    public function response($response, $action, &$output_headers)
    {
        $this->lastResponse = $response;
        try {
            $response = $this->__soapCall($action, [], null, null, $output_headers);
            $this->setState(function () {
                $this->lastRequest = null;
                $this->lastResponse = null;
            });

            return $response;
        } catch (\SoapFault $fault) {
            $this->setState(function () {
                $this->lastRequest = null;
                $this->lastResponse = null;
            });
            throw $fault;
        }
    }

    private function setState(\Closure $state)
    {
        if ($state instanceof \Closure || \is_callable($state)) {
            $state();
        }

        return $this;
    }
}
