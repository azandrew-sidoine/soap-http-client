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

abstract class SoapRequest implements RequestInterface
{
    /**
     * @var string
     */
    protected $request;

    /**
     * @var string
     */
    protected $location;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var int
     */
    protected $version;

    /**
     * @var int
     */
    protected $oneWay;

    /**
     * SoapRequest constructor.
     *
     * @param string $request
     * @param string $location
     * @param string $action
     */
    public function __construct(
        string $request = null,
        string $location = null,
        string $action = null,
        int $version = \SOAP_1_2,
        int $oneWay = 0
    ) {
        $this->request = $request;
        $this->location = $location;
        $this->action = $action;
        $this->version = $version ?? \SOAP_1_2;
        $this->oneWay = $oneWay;
    }

    public function version()
    {
        return $this->version;
    }

    public function body(): string
    {
        return $this->request;
    }

    public function location(): ?string
    {
        return $this->location;
    }

    public function action(): ?string
    {
        return $this->action;
    }

    public function isSOAP1_1(): bool
    {
        return \SOAP_1_1 === $this->version();
    }

    public function isSOAP1_2(): bool
    {
        return \SOAP_1_2 === $this->version();
    }

    public function isOneWay()
    {
        return \PHP_VERSION_ID >= 80000 ? (bool) $this->oneWay : $this->oneWay;
    }

    /**
     * {@inheritDoc}
     */
    public function setVersion($version): RequestInterface
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Set the SOAP request body.
     *
     * @return self
     */
    public function setBody(string $body): RequestInterface
    {
        $this->request = $body;

        return $this;
    }

    /**
     * Set the SOAP resource location.
     *
     * @return self
     */
    public function setLocation(string $location): RequestInterface
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Set the SOAP request action.
     *
     * @return self
     */
    public function setAction(string $action): RequestInterface
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Set the is one property of the SOAP request.
     *
     * @param int|bool $value
     *
     * @return self
     */
    public function setOneWay($value): RequestInterface
    {
        $this->oneWay = $value;

        return $this;
    }
}
