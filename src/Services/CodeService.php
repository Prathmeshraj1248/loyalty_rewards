<?php
declare(strict_types=1);

namespace App\Services;

use App\Config\Env;

final class CodeService
{
    // 31 characters: digits 2-9 and letters without I, L, O (easy to confuse with 1 and 0).
    private const ALPHABET   = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
    private const BODY_LENGTH = 12;

    /** Returns a new random code like "K7QM-3WPX-9TRD-H". */
    public function generate(): string
    {
        $body = '';
        $max  = strlen(self::ALPHABET) - 1;
        for ($i = 0; $i < self::BODY_LENGTH; $i++) {
            $body .= self::ALPHABET[random_int(0, $max)]; // random_int is cryptographically secure
        }
        $full = $body . $this->checksumChar($body);

        return substr($full, 0, 4) . '-' . substr($full, 4, 4) . '-' . substr($full, 8, 4) . '-' . substr($full, 12, 1);
    }

    /** Removes dashes and spaces and uppercases, so "k7qm 3wpx..." equals "K7QM-3WPX-...". */
    public function normalize(string $input): string
    {
        return strtoupper((string) preg_replace('/[\s-]+/', '', $input));
    }

    /** True only if the normalized code has the right length, characters and checksum. */
    public function isWellFormed(string $normalized): bool
    {
        if (!preg_match('/^[2-9A-HJKMNP-Z]{13}$/', $normalized)) {
            return false;
        }
        $body = substr($normalized, 0, self::BODY_LENGTH);
        return hash_equals($this->checksumChar($body), $normalized[self::BODY_LENGTH]);
    }

    /** The value stored in the database. Without CODE_HASH_SECRET this cannot be recreated. */
    public function hash(string $normalized): string
    {
        $secret = Env::get('CODE_HASH_SECRET', '');
        if ($secret === '' || $secret === 'change_me_to_a_long_random_string') {
            throw new \RuntimeException('CODE_HASH_SECRET is not set in .env');
        }
        return hash_hmac('sha256', $normalized, $secret);
    }

    private function checksumChar(string $body): string
    {
        $sum = 0;
        foreach (str_split($body) as $i => $char) {
            $sum += strpos(self::ALPHABET, $char) * ($i + 1); // position-weighted, so swapped characters are caught
        }
        return self::ALPHABET[$sum % strlen(self::ALPHABET)];
    }
}