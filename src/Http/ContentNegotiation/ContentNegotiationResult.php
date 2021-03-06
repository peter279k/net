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

/**
 * Defines the results of content negotiation
 */
final class ContentNegotiationResult
{
    /** @var IMediaTypeFormatter|null The matched media type formatter if there was one, otherwise null */
    private $formatter;
    /** @var string|null The matched media type, or null if no media type was specified */
    private $mediaType;
    /** @var string|null The matched encoding, or null if no encoding was specified */
    private $encoding;
    /** @var string|null The matched language, or null if no language was matched */
    private $language;

    /**
     * @param IMediaTypeFormatter|null $formatter The matched media type formatter if there was one, otherwise null
     * @param string|null $mediaType The matched media type if there was one, otherwise null
     * @param string|null $encoding The matched encoding if there was one, otherwise null
     * @param string|null $language The matched language if there was one, otherwise null
     */
    public function __construct(
        ?IMediaTypeFormatter $formatter,
        ?string $mediaType,
        ?string $encoding,
        ?string $language
    ) {
        $this->formatter = $formatter;
        $this->mediaType = $mediaType;
        $this->encoding = $encoding;
        $this->language = $language;
    }

    /**
     * Gets the matched encoding
     *
     * @return string|null The matched encoding if there was one, otherwise null
     */
    public function getEncoding(): ?string
    {
        return $this->encoding;
    }

    /**
     * Gets the matched media type formatter
     *
     * @return IMediaTypeFormatter|null The matched media type formatter if there was one, otherwise null
     */
    public function getFormatter(): ?IMediaTypeFormatter
    {
        return $this->formatter;
    }

    /**
     * Gets the matched language
     *
     * @return string|null The matched language if there was one, otherwise null
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * Gets the matched media type
     *
     * @return string|null The matched media type if there was one, otherwise null
     */
    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }
}
