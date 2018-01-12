<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http;

use Opulence\IO\Streams\MultiStream;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\MultipartBody;
use Opulence\Net\Http\MultipartBodyPart;
use Opulence\Net\Http\StringBody;

/**
 * Tests the multipart body
 */
class MultipartBodyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that getting the boundary returns the boundary specified in the constructor
     */
    public function testGettingBoundaryReturnsBoundarySpecifiedInConstructor() : void
    {
        $body = new MultipartBody([], 'foo');
        $this->assertEquals('foo', $body->getBoundary());
    }

    /**
     * Tests that getting the boundary returns a UUID when non is specified in the constructor
     */
    public function testGettingBoundaryReturnsUuidWhenNoneSpecifiedInConstructor() : void
    {
        $body = new MultipartBody([]);
        $this->assertNotEmpty($body->getBoundary());
    }

    /**
     * Tests that getting the parts returns the parts
     */
    public function testGettingPartsReturnsParts() : void
    {
        $parts = [
            $this->createMultipartBodyPart(['Foo' => 'bar'], 'baz'),
            $this->createMultipartBodyPart(['Oh' => 'hi'], 'mark')
        ];
        $body = new MultipartBody($parts, '123');
        $this->assertSame($parts, $body->getParts());
    }

    /**
     * Tests that no parts results in only the header and footer
     */
    public function testNoPartsResultsInOnlyHeaderAndFooter() : void
    {
        $body = new MultipartBody([], '123');
        $this->assertEquals("--123\r\n--123--", (string)$body);
    }

    /**
     * Tests that the parts are written to a stream with boundaries
     */
    public function testPartsAreWrittenToStreamWithBoundaries() : void
    {
        $parts = [
            $this->createMultipartBodyPart(['Foo' => 'bar'], 'baz'),
            $this->createMultipartBodyPart(['Oh' => 'hi'], 'mark')
        ];
        $body = new MultipartBody($parts, '123');
        $this->assertEquals("--123\r\nFoo: bar\r\n\r\nbaz\r\n--123\r\nOh: hi\r\n\r\nmark\r\n--123--", (string)$body);
    }

    /**
     * Tests that reading as a stream returns a multi-stream
     */
    public function testReadingAsStreamReturnsAMultiStream() : void
    {
        $parts = [
            $this->createMultipartBodyPart(['Foo' => 'bar'], 'baz'),
            $this->createMultipartBodyPart(['Oh' => 'hi'], 'mark')
        ];
        $body = new MultipartBody($parts, '123');
        $this->assertInstanceOf(MultiStream::class, $body->readAsStream());
    }

    /**
     * Tests that a single part is wrapped with a header and footer
     */
    public function testSinglePartIsWrappedWithHeaderAndFooter() : void
    {
        $parts = [
            $this->createMultipartBodyPart(['Foo' => 'bar'], 'baz')
        ];
        $body = new MultipartBody($parts, '123');
        $this->assertEquals("--123\r\nFoo: bar\r\n\r\nbaz\r\n--123--", (string)$body);
    }

    /**
     * Creates a multipart body part for use in tests
     *
     * @param array $rawHeaders The headers to use
     * @param string $body The body to use
     * @return MultipartBodyPart The multipart body part
     */
    private function createMultipartBodyPart(array $rawHeaders, string $body) : MultipartBodyPart
    {
        $headers = new HttpHeaders();

        foreach ($rawHeaders as $name => $value) {
            $headers->add($name, $value);
        }

        return new MultipartBodyPart($headers, new StringBody($body));
    }
}
