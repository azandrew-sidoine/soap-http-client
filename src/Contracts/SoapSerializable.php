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

interface SoapSerializable
{
    /**
     * Convert the object or it properties to an soap xml string or an soap xml object.
     *
     * @return string|mixed
     */
    public function serialize();
}
