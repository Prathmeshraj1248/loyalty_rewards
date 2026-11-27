<?php
declare(strict_types=1);

use App\Services\CodeService;

final class ClaimServiceTest extends DbTestCase
{
    public function test_valid_code_awards_coins_once(): void
    {
        $user = $this->makeUser();
        [$code] = $this->makeCodes();

        $this->assertTrue($this->claimService()->claim($user, $code, '1.1.1.1')['ok']);
        $this->assertSame(10, $this->balance($user));

        $again = $this->claimService()->claim($user, $code, '1.1.1.1');
        $this->assertFalse($again['ok']);
        $this->assertSame(10, $this->balance($user));
    }

    public function test_another_user_cannot_reuse_a_claimed_code(): void
    {
        [$a, $b] = [$this->makeUser(), $this->makeUser()];
        [$code] = $this->makeCodes();

        $this->claimService()->claim($a, $code, '1.1.1.1');
        $this->assertFalse($this->claimService()->claim($b, $code, '2.2.2.2')['ok']);

        $this->assertSame(0, $this->balance($b));
        $this->assertSame(1, (int) $this->pdo->query("SELECT COUNT(*) FROM point_ledger WHERE entry_type = 'earn'")->fetchColumn());
    }

    public function test_unknown_and_malformed_codes_are_rejected(): void
    {
        $user = $this->makeUser();
        $neverSaved = (new CodeService())->generate(); // well-formed, but not in the database

        $this->assertFalse($this->claimService()->claim($user, $neverSaved, '1.1.1.1')['ok']);
        $this->assertFalse($this->claimService()->claim($user, 'HELLO', '1.1.1.1')['ok']);
        $this->assertSame(0, $this->balance($user));
    }

    public function test_blocked_code_is_rejected(): void
    {
        $user = $this->makeUser();
        [$code] = $this->makeCodes();
        $this->pdo->exec("UPDATE product_codes SET status = 'blocked'");

        $result = $this->claimService()->claim($user, $code, '1.1.1.1');
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('blocked', $result['message']);
        $this->assertSame(0, $this->balance($user));
    }

    public function test_every_attempt_is_logged(): void
    {
        $user = $this->makeUser();
        [$code] = $this->makeCodes();

        $this->claimService()->claim($user, $code, '1.1.1.1');
        $this->claimService()->claim($user, $code, '1.1.1.1');
        $this->claimService()->claim($user, 'HELLO', '1.1.1.1');

        $results = $this->pdo->query('SELECT result FROM claim_attempts ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
        $this->assertSame(['success', 'already_claimed', 'invalid'], $results);
    }

    public function test_rate_limit_stops_guessing_even_for_a_real_code(): void
    {
        $user = $this->makeUser();
        [$code] = $this->makeCodes();

        for ($i = 0; $i < 5; $i++) {
            $this->claimService()->claim($user, 'BADCODE', '1.1.1.1');
        }
        $result = $this->claimService()->claim($user, $code, '1.1.1.1');

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Too many', $result['message']);
        $this->assertSame('unused', $this->pdo->query('SELECT status FROM product_codes')->fetchColumn());
    }
}