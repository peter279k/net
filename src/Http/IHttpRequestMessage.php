<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

use Opulence\Collections\IDictionary;
use Opulence\Net\Uri;

/**
 * Defines the interface for HTTP request messages to implement
 */
interface IHttpRequestMessage extends IHttpMessage
{
    /**
     * Gets the HTTP method for the request
     *
     * @return string The HTTP method
     */
    public function getMethod(): string;

    /**
     * Gets the properties of the request
     * These are custom pieces of metadata that the application can attach to the request
     *
     * @return IDictionary The collection of properties
     */
    public function getProperties(): IDictionary;

    /**
     * Gets the URI of the request
     *
     * @return Uri The URI
     */
    public function getUri(): Uri;
}
