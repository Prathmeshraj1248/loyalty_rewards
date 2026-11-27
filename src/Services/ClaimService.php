<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ClaimRepository;

final class ClaimService
{
    public function __construct(
        private ClaimRepository $claims,
        private CodeService $codes
    ) {
    }

    /**
     * The only place that decides whether a code is valid and whether coins are awarded.
     *
     * @return array{ok: bool, message: string}
     */
    public function claim(int $userId, string $input, string $ip): array
    {
        // 0. Too many recent failures? Refuse BEFORE looking at the code at all.
        [$byUser, $byIp] = $this->claims->failureCounts($userId, $ip);
        if ($byUser >= 5 || $byIp >= 20) {
            $this->claims->logAttempt($userId, null, 'rate_limited', $ip);
            return ['ok' => false, 'message' => 'Too many wrong attempts. Please wait 15 minutes and try again.'];
        }

        // 1. Cheap check first: right length, characters and checksum. No database needed.
        $code = $this->codes->normalize($input);
        if (!$this->codes->isWellFormed($code)) {
            $this->claims->logAttempt($userId, null, 'invalid', $ip);
            return $this->failure('invalid');
        }

        $hash = $this->codes->hash($code);

        // 2. Lock the code row, decide, and (if allowed) award the coins, all in one transaction.
        $codeId  = null;
        $coins   = 0;
        $this->claims->begin();
        try {
            $row = $this->claims->findCodeForUpdate($hash);

            $result = match (true) {
                $row === null                => 'invalid',
                $row['status'] === 'claimed' => 'already_claimed',
                $row['status'] === 'blocked' => 'blocked',
                default                      => 'success',
            };

            if ($row !== null) {
                $codeId = (int) $row['id'];
            }

            if ($result === 'success') {
                $coins = $this->claims->coinsForBatch((int) $row['batch_id']);
                if (!$this->claims->markClaimed($codeId, $userId)) {
                    throw new \LogicException('Code changed while it was locked.');
                }
                $this->claims->addEarnEntry($userId, $coins, $codeId);
            }

            $this->claims->commit();
        } catch (\Throwable $e) {
            $this->claims->rollBack(); // nothing is half-saved
            throw $e;
        }

        // 3. Log after the transaction so the log row is saved whatever the outcome was.
        $this->claims->logAttempt($userId, $codeId, $result, $ip);

        if ($result !== 'success') {
            return $this->failure($result);
        }
        return [
            'ok'      => true,
            'message' => "You earned $coins " . ($coins === 1 ? 'coin' : 'coins') . '!',
        ];
    }

    /** @return array{ok: bool, message: string} */
    private function failure(string $result): array
    {
        $messages = [
            'invalid'         => 'That code is not valid. Check the characters and try again.',
            'already_claimed' => 'This code has already been used.',
            'blocked'         => 'This code has been blocked. Please contact support.',
        ];
        return ['ok' => false, 'message' => $messages[$result] ?? 'Something went wrong.'];
    }
}
