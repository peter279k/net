<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting;

use InvalidArgumentException;
use Opulence\Net\Http\Headers\AcceptCharSetHeaderValue;
use Opulence\Net\Http\Headers\AcceptMediaTypeHeaderValue;
use Opulence\Net\Http\Headers\ContentTypeHeaderValue;
use Opulence\Net\Http\Headers\IHeaderValueWithQualityScore;
use Opulence\Net\Http\Headers\MediaTypeHeaderValue;
use Opulence\Net\Http\IHttpRequestMessage;

/**
 * Defines the default content negotiator
 */
class ContentNegotiator implements IContentNegotiator
{
    /** @const The default media type if none is found (RFC-2616) */
    private const DEFAULT_MEDIA_TYPE = 'application/octet-stream';
    /** @var IMediaTypeFormatter[] The list of registered formatters */
    private $formatters;
    /** @var RequestHeaderParser The header parser */
    private $headerParser;

    /**
     * @param IMediaTypeFormatter[] $formatters The list of formatters
     * @param RequestHeaderParser|null $headerParser The header parser, or null if using the default one
     * @throws InvalidArgumentException Thrown if the list of formatters is empty
     */
    public function __construct(array $formatters, RequestHeaderParser $headerParser = null)
    {
        if (count($formatters) === 0) {
            throw new InvalidArgumentException('List of formatters must not be empty');
        }

        $this->formatters = $formatters;
        $this->headerParser = $headerParser ?? new RequestHeaderParser();
    }

    /**
     * @inheritdoc
     */
    public function negotiateRequestContent(IHttpRequestMessage $request) : ?ContentNegotiationResult
    {
        $requestHeaders = $request->getHeaders();

        if (!$requestHeaders->containsKey('Content-Type')) {
            // Default to the first registered media type formatter
            return new ContentNegotiationResult(
                $this->formatters[0],
                self::DEFAULT_MEDIA_TYPE,
                null
            );
        }

        $parsedContentTypeHeader = $this->headerParser->parseParameters($requestHeaders, 'Content-Type', 0);
        // The first value should be the content-type
        $contentType = $parsedContentTypeHeader->getKeys()[0];
        $charSet = $parsedContentTypeHeader->containsKey('charset') ? $parsedContentTypeHeader['charset'] : null;
        // Todo: I need to create a result that includes a charset - I'm simplifying this for now
        $mediaTypeFormatterMatch = $this->getBestMediaTypeFormatterMatch(new ContentTypeHeaderValue($contentType));

        if ($mediaTypeFormatterMatch !== null) {
            return new ContentNegotiationResult(
                $mediaTypeFormatterMatch->getFormatter(),
                $mediaTypeFormatterMatch->getMediaTypeHeaderValue()->getMediaType(),
                null
            );
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function negotiateResponseContent(IHttpRequestMessage $request) : ?ContentNegotiationResult
    {
        $requestHeaders = $request->getHeaders();
        $charSetHeaders = $this->headerParser->parseAcceptCharsetHeader($requestHeaders);
        // Todo: $rankedCharSetHeaders = $this->rankCharSetHeaders($charSetHeaders);

        if (!$requestHeaders->containsKey('Accept')) {
            // Todo: We have to use the parser to grab all charsets, then rank them
            // Todo: We should them match them against the default formatter's supported encodings
            // Todo: If there are no matches, then $charSet should be null
            // Todo: $charSet = $this->getMatchingCharSet($this->formatters[0], $rankedCharSetHeaders);

            // Default to the first registered media type formatter
            return new ContentNegotiationResult(
                $this->formatters[0],
                self::DEFAULT_MEDIA_TYPE,
                null// Todo: $charSet
            );
        }

        $mediaTypeHeaders = $this->headerParser->parseAcceptHeader($requestHeaders);
        $rankedMediaTypeHeaders = $this->rankMediaTypeHeaders($mediaTypeHeaders);

        foreach ($rankedMediaTypeHeaders as $mediaTypeHeader) {
            $mediaTypeFormatterMatch = $this->getBestMediaTypeFormatterMatch($mediaTypeHeader);

            if ($mediaTypeFormatterMatch !== null) {
                // Todo: I need to create a result that includes a charset - I'm simplifying this for now
                return new ContentNegotiationResult(
                    $mediaTypeFormatterMatch->getFormatter(),
                    $mediaTypeFormatterMatch->getMediaTypeHeaderValue()->getMediaType(),
                    null
                );
            }
        }

        return null;
    }

    /**
     * Compares two charsets and returns which of them is "lower" than the other
     *
     * @param AcceptCharSetHeaderValue $a The first charset header to compare
     * @param AcceptCharSetHeaderValue $b The second charset header to compare
     * @return int -1 if $a is lower than $b, 0 if they're even, or 1 if $a is higher than $b
     */
    protected function compareCharSets(AcceptCharSetHeaderValue $a, AcceptCharSetHeaderValue $b) : int
    {
        $aQuality = $a->getQuality();
        $bQuality = $b->getQuality();

        if ($aQuality < $bQuality) {
            return 1;
        }

        if ($aQuality > $bQuality) {
            return -1;
        }

        $aValue = $a->getCharSet();
        $bValue = $b->getCharSet();

        if ($aValue === '*') {
            if ($bValue === '*') {
                return 0;
            }

            return 1;
        }

        if ($bValue === '*') {
            return -1;
        }

        return 0;
    }

    /**
     * Compares two media types and returns which of them is "lower" than the other
     *
     * @param AcceptMediaTypeHeaderValue $a The first media type to compare
     * @param AcceptMediaTypeHeaderValue $b The second media type to compare
     * @return int -1 if $a is lower than $b, 0 if they're even, or 1 if $a is higher than $b
     */
    protected function compareMediaTypes(AcceptMediaTypeHeaderValue $a, AcceptMediaTypeHeaderValue $b) : int
    {
        $aQuality = $a->getQuality();
        $bQuality = $b->getQuality();

        if ($aQuality < $bQuality) {
            return 1;
        }

        if ($aQuality > $bQuality) {
            return -1;
        }

        $aType = $a->getType();
        $bType = $b->getType();
        $aSubType = $a->getSubType();
        $bSubType = $b->getSubType();

        if ($aType === '*') {
            if ($bType === '*') {
                return 0;
            }

            return 1;
        }

        if ($aSubType === '*') {
            if ($bSubType === '*') {
                return 0;
            }

            return 1;
        }

        // If we've gotten here, then $a had no wildcards
        if ($bType === '*' || $bSubType === '*') {
            return -1;
        }

        return 0;
    }

    /**
     * Filters out any header values with a zero quality score
     *
     * @param IHeaderValueWithQualityScore $headerValue The value to check
     * @return bool True if we should keep the value, otherwise false
     */
    protected function filterZeroScores(IHeaderValueWithQualityScore $headerValue) : bool
    {
        return $headerValue->getQuality() > 0;
    }

    /**
     * Gets the best charset for the input media type formatter
     *
     * @param IHttpRequestMessage $request The request to match with
     * @param IMediaTypeFormatter $formatter The media type formatter to match against
     * @return string|null The best charset if one was found, otherwise null
     */
    protected function getBestCharset(IHttpRequestMessage $request, IMediaTypeFormatter $formatter) : ?string
    {
        return null;
    }

    /**
     * Gets the best media type formatter match
     *
     * @param MediaTypeHeaderValue $mediaTypeHeaderValue The media type header value to match on
     * @return MediaTypeFormatterMatch|null The best media type formatter match if one was found, otherwise null
     * @throws InvalidArgumentException Thrown if the media type was incorrectly formatted
     */
    protected function getBestMediaTypeFormatterMatch(MediaTypeHeaderValue $mediaTypeHeaderValue) : ?MediaTypeFormatterMatch
    {
        $mediaTypeParts = explode('/', $mediaTypeHeaderValue->getMediaType());

        // Don't bother going on if the media type isn't in the correct format
        if (count($mediaTypeParts) !== 2 || $mediaTypeParts[0] === '' || $mediaTypeParts[1] === '') {
            throw new InvalidArgumentException('Media type must be in format {type}/{sub-type}');
        }

        [$type, $subType] = $mediaTypeParts;

        foreach ($this->formatters as $formatter) {
            foreach ($formatter->getSupportedMediaTypes() as $supportedMediaType) {
                [$supportedType, $supportedSubType] = explode('/', $supportedMediaType);

                // Checks if the type is a wildcard or a match and the sub-type is a wildcard or a match
                if (
                    $type === '*' ||
                    ($subType === '*' && $type === $supportedType) ||
                    ($type === $supportedType && $subType === $supportedSubType)
                ) {
                    return new MediaTypeFormatterMatch(
                        $formatter,
                        new MediaTypeHeaderValue($supportedMediaType, $mediaTypeHeaderValue->getParameters())
                    );
                }
            }
        }

        return null;
    }

    /**
     * Ranks the charset headers by quality, and then by specificity
     *
     * @param AcceptCharSetHeaderValue[] $charSetHeaders The list of charset headers to rank
     * @return AcceptCharSetHeaderValue[] The ranked list of charset headers
     */
    protected function rankCharSetHeaders(array $charSetHeaders) : array
    {
        usort($charSetHeaders, [$this, 'compareCharSets']);
        $rankedCharsetHeaders = array_filter($charSetHeaders, [$this, 'filterZeroScores']);

        // Have to return the values because the keys aren't updated in array_filter()
        return array_values($rankedCharsetHeaders);
    }

    /**
     * Ranks the media type headers by quality, and then by specificity
     *
     * @param AcceptMediaTypeHeaderValue[] $mediaTypeHeaders The list of media type headers to rank
     * @return AcceptMediaTypeHeaderValue[] The ranked list of media type headers
     */
    protected function rankMediaTypeHeaders(array $mediaTypeHeaders) : array
    {
        usort($mediaTypeHeaders, [$this, 'compareMediaTypes']);
        $rankedMediaTypeHeaders = array_filter($mediaTypeHeaders, [$this, 'filterZeroScores']);

        // Have to return the values because the keys aren't updated in array_filter()
        return array_values($rankedMediaTypeHeaders);
    }
}
