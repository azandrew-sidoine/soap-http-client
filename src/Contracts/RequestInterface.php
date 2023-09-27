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

interface RequestInterface
{
    /**
     * SOAP request body.
     */
    public function body(): string;

    /**
     * SOAP request resource location parameter.
     *
     * @return string
     */
    public function location(): ?string;

    /**
     * Callable SOAP action.
     *
     * @return string
     */
    public function action(): ?string;

    /**
     * SOAP client version to use.
     *
     * @return string|int
     */
    public function version();

    /**
     * Indicates whether the request expect a response to be returned or not.
     *
     * @return bool|int
     */
    public function isOneWay();

    /**
     * Set the SOAP Request version property.
     *
     * @param string|int $version
     */
    public function setVersion($version): self;

    /**
     * Set the SOAP request body.
     */
    public function setBody(string $body): self;

    /**
     * Set the SOAP resource location.
     */
    public function setLocation(string $location): self;

    /**
     * Set the SOAP request action.
     */
    public function setAction(string $action): self;

    /**
     * Set the is one property of the SOAP request.
     *
     * @param int|bool $value
     */
    public function setOneWay($value): self;
}
