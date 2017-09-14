<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Responses;

use Opulence\IO\Streams\IStream;
use Opulence\IO\Streams\Stream;

/**
 * Defines the response writer
 */
class ResponseWriter
{
    /** @var IStream The output stream to write to */
    private $outputStream = null;

    /**
     * @param IStream|null $outputStream The output stream to write to (null defaults to PHP's output stream)
     */
    public function __construct(IStream $outputStream = null)
    {
        $this->outputStream = $outputStream ?? new Stream(fopen('php://output', 'r+'));
    }

    /**
     * Writes the response to the output stream
     *
     * @param IHttpResponseMessage $response The response to write
     */
    public function writeResponse(IHttpResponseMessage $response) : void
    {
        // Todo: Write the status code, reason phrase, and any headers
        $response->getBody()->writeToStream($this->outputStream);
    }
}