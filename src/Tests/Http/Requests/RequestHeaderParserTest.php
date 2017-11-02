<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Requests;

use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\Requests\RequestHeaderParser;

/**
 * Tests the HTTP request header parser
 */
class RequestHeaderParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestHeaderParser The parser to use in tests */
    private $parser = null;
    /** @var HttpHeaders The headers to use in tests */
    private $headers = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->parser = new RequestHeaderParser();
        $this->headers = new HttpHeaders();
    }

    /**
     * Tests checking if the headers indicate a JSON response with the value of the content type header
     */
    public function testCheckingIfJsonChecksContentTypeHeader() : void
    {
        $this->headers->add('Content-Type', 'text/plain');
        $this->assertFalse($this->parser->isJson($this->headers));
        $this->headers->removeKey('Content-Type');
        $this->headers->add('Content-Type', 'application/json');
        $this->assertTrue($this->parser->isJson($this->headers));
        $this->headers->removeKey('Content-Type');
        $this->headers->add('Content-Type', 'application/json; charset=utf-8');
        $this->assertTrue($this->parser->isJson($this->headers));
    }

    /**
     * Tests checking if the headers indicate a multipart response with the value of the content type header
     */
    public function testCheckingIfMultipartChecksContentTypeHeader() : void
    {
        $this->headers->add('Content-Type', 'text/plain');
        $this->assertFalse($this->parser->isMultipart($this->headers));
        $this->headers->removeKey('Content-Type');
        $this->headers->add('Content-Type', 'multipart/mixed');
        $this->assertTrue($this->parser->isMultipart($this->headers));
        $this->headers->removeKey('Content-Type');
        $this->headers->add('Content-Type', 'multipart/form-data');
        $this->assertTrue($this->parser->isMultipart($this->headers));
    }

    /**
     * Tests checking if the headers indicate an XHR request with the value of the X-Requested-With header
     */
    public function testCheckingIfXhrChecksXRequestedWithHeader() : void
    {
        $this->headers->add('X-Requested-With', 'XMLHttpRequest');
        $this->assertTrue($this->parser->isXhr($this->headers));
        $this->headers->removeKey('X-Requested-With');
        $this->assertFalse($this->parser->isXhr($this->headers));
    }
}