<?php
declare(strict_types=1);

namespace App\Services;

use App\Config\Env;
use PDO;

final class InsightService
{
    public function __construct(private PDO $pdo)
    {
    }

    /** Plain rules, no AI. These decide what looks suspicious. */
    public function flags(): array
    {
        $out = [];
        $q = $this->pdo->query(
            "SELECT user_id, COUNT(*) AS n FROM claim_attempts
             WHERE user_id IS NOT NULL AND result IN ('invalid','already_claimed','blocked') AND created_at >= (NOW() - INTERVAL 1 DAY)
             GROUP BY user_id HAVING n >= 5 ORDER BY n DESC LIMIT 10"
        );
        foreach ($q as $r) {
            $out[] = "Customer #{$r['user_id']} made {$r['n']} failed code attempts.";
        }
        $q = $this->pdo->query(
            "SELECT COUNT(DISTINCT user_id) AS n FROM claim_attempts
             WHERE user_id IS NOT NULL AND created_at >= (NOW() - INTERVAL 1 DAY)
             GROUP BY ip_address HAVING n >= 3 ORDER BY n DESC LIMIT 5"
        );
        foreach ($q as $r) {
            $out[] = "One IP address was used by {$r['n']} different accounts.";
        }
        $n = (int) $this->pdo->query("SELECT COUNT(*) FROM claim_attempts WHERE result = 'rate_limited' AND created_at >= (NOW() - INTERVAL 1 DAY)")->fetchColumn();
        if ($n > 0) {
            $out[] = "$n attempts were blocked by the rate limiter.";
        }
        return $out;
    }

    /** @return array{source: string, text: string}. Falls back to plain text if the AI is unavailable. */
    public function summarise(array $flags): array
    {
        $fallback = ['source' => 'Rule-based', 'text' => $flags
            ? count($flags) . ' signal(s) need review. Check the failed attempts and consider blocking the listed customers.'
            : 'No suspicious activity in the last 24 hours.'];

        $key = Env::get('AI_API_KEY', '');
        if ($key === '' || !$flags || !function_exists('curl_init')) {
            return $fallback;
        }

        // Only anonymous counts are sent: no emails, names or IP addresses.
        $prompt = "A factory owner runs a QR-code rewards system. In 3 short sentences, summarise these fraud signals "
                . "and say what to check first. Do not invent facts.\n- " . implode("\n- ", $flags);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => ['content-type: application/json', 'x-api-key: ' . $key, 'anthropic-version: 2023-06-01'],
            CURLOPT_POSTFIELDS     => json_encode([
                'model'      => Env::get('AI_MODEL', 'claude-haiku-5-5'),
                'max_tokens' => 300,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]),
        ]);
        $body = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($body === false) {
            error_log('AI call failed: ' . curl_error($ch));
        }
        curl_close($ch);

        $text = is_string($body) ? (json_decode($body, true)['content'][0]['text'] ?? null) : null;
        return ($http === 200 && is_string($text)) ? ['source' => 'AI', 'text' => $text] : $fallback;
    }
}