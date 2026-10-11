<?php

namespace App\Support;

final class SearchTermParser
{
    public const MIN_FULLTEXT_TOKEN_LENGTH = 3;

    private const MAX_TOKENS = 8;

    /** @var list<string> */
    private array $tokens;

    public function __construct(string $raw)
    {
        // Remove all non-alphanumeric characters (except whitespace) and split into tokens
        $clean = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $raw) ?? '';
        $tokens = preg_split('/\s+/u', mb_strtolower(trim($clean)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $this->tokens = array_slice(array_values(array_unique($tokens)), 0, self::MAX_TOKENS);
    }

    public function isEmpty(): bool
    {
        return $this->tokens === [];
    }

    /**
     * Returns a string suitable for a boolean full-text search query, with all usable tokens prefixed with "+" and suffixed with "*".
     * Tokens shorter than MIN_FULLTEXT_TOKEN_LENGTH are ignored.
     */
    public function toBooleanQuery(): ?string
    {
        $usable = array_filter(
            $this->tokens,
            fn (string $t) => mb_strlen($t) >= self::MIN_FULLTEXT_TOKEN_LENGTH
        );

        if ($usable === []) {
            return null;
        }

        return implode(' ', array_map(fn (string $t) => "+{$t}*", $usable));
    }

    /**
     * Returns a string suitable for a LIKE query, with all tokens joined by spaces and a trailing wildcard.
     */
    public function toLikePrefix(): string
    {
        $joined = implode(' ', $this->tokens);

        return addcslashes($joined, '%_\\').'%';
    }
}
