<?php

declare(strict_types=1);

class JsonHighlighter
{
    private const CLASS_KEY      = 'json-key';      // Class for keys
    private const CLASS_STRING   = 'json-string';   // Class for strings
    private const CLASS_NUMBER   = 'json-number';   // Class for numbers
    private const CLASS_BOOL     = 'json-bool';     // Class for boolean values (true, false)
    private const CLASS_NULL     = 'json-null';     // Class for null
    private const CLASS_BRACKETS = 'json-brackets'; // Class for brackets/braces/commas/colons
    private const CLASS_COLON    = 'json-colon';    // Class for colons
    private const CLASS_COMMA    = 'json-comma';    // Class for commas
    private const STYLE_RESET    = '</span>';       // Closing the span tag
    // phpcs:ignore
    const JSON_HIGHLIGHT_REGEX = '/(?<key>"[^"]*")\s*:\s*|(?<string>"[^"\\\\]*(?:\\\\.[^"\\\\]*)*")|(?<number>-?\d+(?:\.\d+)?)|\b(?<bool>true|false)\b|\b(?<null>null)\b|(?<brackets>[{}[\]])|(?<colon>:)|(?<comma>,)/';

    /**
     * Highlight JSON and return the HTML string.
     */
    public static function highlight(string $json): string
    {
        try {
            $decodedJson = json_decode($json, true, flags: \JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return $json;
        }

        // Use JSON_UNESCAPED_SLASHES to prevent the escaping of slashes
        $json = \json_encode($decodedJson, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);

        return \preg_replace_callback(
                self::JSON_HIGHLIGHT_REGEX,
                [self::class, 'replaceCallback'],
                $json
            );
    }

    private static function replaceCallback(array $matches): string
    {
        if ($matches['key'] !== '') {
            return self::wrapInSpan($matches['key'], self::CLASS_KEY) . self::wrapInSpan(': ', self::CLASS_COLON);
        }

        if ($matches['string'] !== '') {
            return self::wrapInSpan($matches['string'], self::CLASS_STRING);
        }

        if ($matches['number'] !== '') {
            return self::wrapInSpan($matches['number'], self::CLASS_NUMBER);
        }

        if ($matches['bool'] !== '') {
            return self::wrapInSpan($matches['bool'], self::CLASS_BOOL);
        }

        if ($matches['null'] !== '') {
            return self::wrapInSpan($matches['null'], self::CLASS_NULL);
        }

        if ($matches['brackets'] !== '') {
            return self::wrapInSpan($matches['brackets'], self::CLASS_BRACKETS);
        }

        if ($matches['colon'] !== '') {
            return self::wrapInSpan($matches['colon'], self::CLASS_COLON);
        }

        if ($matches['comma'] !== '') {
            return self::wrapInSpan($matches['comma'], self::CLASS_COMMA);
        }

        return $matches[0];
    }

    private static function wrapInSpan(string $text, string $class): string
    {
        return '<span class="' . $class . '">' . $text . self::STYLE_RESET;
    }
}
