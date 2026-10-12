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
        $clean = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $raw) ?? '';
        $tokens = preg_split('/\s+/u', mb_strtolower(trim($clean)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $this->tokens = array_slice(array_values(array_unique($tokens)), 0, self::MAX_TOKENS);
    }

    public function isEmpty(): bool
    {
        return $this->tokens === [];
    }

    public function toBooleanQuery(): ?string
    {
        $usable = array_filter(
            $this->tokens,
            fn (string $token) => mb_strlen($token) >= self::MIN_FULLTEXT_TOKEN_LENGTH
        );

        if ($usable === []) {
            return null;
        }

        return implode(' ', array_map(fn (string $token) => "+{$token}*", $usable));
    }

    /**
     * Returns a collection of escaped LIKE terms, one per token.
     * This is used for a safer fallback search that matches both name and description.
     *
     * @return list<string>
     */
    public function toLikeTerms(): array
    {
        return array_map(
            fn (string $token) => addcslashes($token, '%_\\') . '%',
            $this->tokens
        );
    }

    public function toLikePrefix(): string
    {
        $joined = implode(' ', $this->tokens);

        return addcslashes($joined, '%_\\') . '%';
    }
}
