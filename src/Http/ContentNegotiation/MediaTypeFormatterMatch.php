<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\ContentNegotiation;

use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\Net\Http\Headers\MediaTypeHeaderValue;

/**
 * Defines a media type formatter match
 */
final class MediaTypeFormatterMatch
{
    /** @var IMediaTypeFormatter The matched media type formatter */
    private $formatter;
    /** @var string The matched media type */
    private $mediaType;
    /** @var string The matched media type header */
    private $mediaTypeHeaderValue;

    /**
     * @param IMediaTypeFormatter $formatter The matched media type formatter
     * @param string $mediaType The matched media type
     * @param MediaTypeHeaderValue $mediaTypeHeaderValue The matched media type header value
     */
    public function __construct(IMediaTypeFormatter $formatter, string $mediaType, MediaTypeHeaderValue $mediaTypeHeaderValue)
    {
        $this->formatter = $formatter;
        $this->mediaType = $mediaType;
        $this->mediaTypeHeaderValue = $mediaTypeHeaderValue;
    }

    /**
     * Gets the matched media type formatter
     *
     * @return IMediaTypeFormatter The matched media type formatter
     */
    public function getFormatter(): IMediaTypeFormatter
    {
        return $this->formatter;
    }

    /**
     * Gets the matched media type
     *
     * @return string The matched media type
     */
    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    /**
     * Gets the matched media type header value
     *
     * @return MediaTypeHeaderValue The matched media type header value
     */
    public function getMediaTypeHeaderValue(): MediaTypeHeaderValue
    {
        return $this->mediaTypeHeaderValue;
    }
}
