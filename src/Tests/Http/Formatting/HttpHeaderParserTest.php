<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use Opulence\Collections\ImmutableHashTable;
use Opulence\Net\Http\Formatting\HttpHeaderParser;
use Opulence\Net\Http\HttpHeaders;

/**
 * Tests the HTTP header parser
 */
class HttpHeaderParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var HttpHeaderParser The parser to use in tests */
    private $parser = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->parser = new HttpHeaderParser();
    }

    /**
     * Tests checking if the headers indicate a JSON response with the value of the content type header
     */
    public function testCheckingIfJsonChecksContentTypeHeader() : void
    {
        $headers = new HttpHeaders();
        $headers->add('Content-Type', 'text/plain');
        $this->assertFalse($this->parser->isJson($headers));
        $headers->removeKey('Content-Type');
        $headers->add('Content-Type', 'application/json');
        $this->assertTrue($this->parser->isJson($headers));
        $headers->removeKey('Content-Type');
        $headers->add('Content-Type', 'application/json; charset=utf-8');
        $this->assertTrue($this->parser->isJson($headers));
    }

    /**
     * Tests checking if the headers indicate a multipart response with the value of the content type header
     */
    public function testCheckingIfMultipartChecksContentTypeHeader() : void
    {
        $headers = new HttpHeaders();
        $headers->add('Content-Type', 'text/plain');
        $this->assertFalse($this->parser->isMultipart($headers));
        $headers->removeKey('Content-Type');
        $headers->add('Content-Type', 'multipart/mixed');
        $this->assertTrue($this->parser->isMultipart($headers));
        $headers->removeKey('Content-Type');
        $headers->add('Content-Type', 'multipart/form-data');
        $this->assertTrue($this->parser->isMultipart($headers));
    }

    /**
     * Tests that getting the parameters for an index that does not exist returns an empty dictionary
     */
    public function testGettingParametersForIndexThatDoesNotExistReturnsEmptyDictionary() : void
    {
        $headers = new HttpHeaders();
        $headers->add('Foo', 'bar; baz');
        $this->assertEquals(new ImmutableHashTable([]), $this->parser->parseParameters($headers, 'Foo', 1));
    }

    /**
     * Tests that getting the parameters with a mix of value and value-less parameters returns correct parameters
     */
    public function testGettingParametersWithMixOfValueAndValueLessParametersReturnsCorrectParameters() : void
    {
        $headers = new HttpHeaders();
        $headers->add('Foo', 'bar; baz="blah"');
        $values = $this->parser->parseParameters($headers, 'Foo');
        $this->assertNull($values->get('bar'));
        $this->assertEquals('blah', $values->get('baz'));
    }

    /**
     * Tests getting parameters with quoted and unquoted values returns an array with the unquoted value
     */
    public function testGettingParametersWithQuotedAndUnquotedValuesReturnsArrayWithUnquotedValue() : void
    {
        $headers = new HttpHeaders();
        $headers->add('Foo', 'bar=baz');
        $headers->add('Bar', 'bar="baz"');
        $this->assertEquals('baz', $this->parser->parseParameters($headers, 'Foo')->get('bar'));
        $this->assertEquals('baz', $this->parser->parseParameters($headers, 'Bar')->get('bar'));
    }
}
