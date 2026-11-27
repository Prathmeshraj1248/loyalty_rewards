<?php
declare(strict_types=1);

use App\Services\RedeemService;

final class RedeemServiceTest extends DbTestCase
{
    private function svc(): RedeemService
    {
        return new RedeemService($this->pdo);
    }

    private function fund(int $userId, int $coins): void
    {
        $this->pdo->prepare("INSERT INTO point_ledger (user_id, entry_type, points) VALUES (?, 'earn', ?)")->execute([$userId, $coins]);
    }

    private function requestId(): int
    {
        return (int) $this->pdo->query('SELECT id FROM redemption_requests ORDER BY id DESC LIMIT 1')->fetchColumn();
    }

    public function test_cannot_redeem_more_than_the_balance(): void
    {
        $user = $this->makeUser();
        $this->fund($user, 150);

        $this->assertNotNull($this->svc()->request($user, 200));
        $this->assertSame(150, $this->balance($user));
        $this->assertSame(0, (int) $this->pdo->query('SELECT COUNT(*) FROM redemption_requests')->fetchColumn());
    }

    public function test_below_the_minimum_is_refused(): void
    {
        $user = $this->makeUser();
        $this->fund($user, 500);

        $this->assertStringContainsString('Minimum', (string) $this->svc()->request($user, 50));
        $this->assertSame(500, $this->balance($user));
    }

    public function test_request_puts_the_coins_on_hold(): void
    {
        $user = $this->makeUser();
        $this->fund($user, 300);

        $this->assertNull($this->svc()->request($user, 200));
        $this->assertSame(100, $this->balance($user));
        $this->assertSame('pending', $this->pdo->query('SELECT status FROM redemption_requests')->fetchColumn());
    }

    public function test_reject_refunds_the_coins_only_once(): void
    {
        $user = $this->makeUser();
        $this->fund($user, 300);
        $this->svc()->request($user, 200);
        $id = $this->requestId();

        $this->assertTrue($this->svc()->reject($id, $this->adminId));
        $this->assertSame(300, $this->balance($user));
        $this->assertFalse($this->svc()->reject($id, $this->adminId));
        $this->assertSame(300, $this->balance($user));
    }

    public function test_approved_request_cannot_be_handled_again(): void
    {
        $user = $this->makeUser();
        $this->fund($user, 300);
        $this->svc()->request($user, 200);
        $id = $this->requestId();

        $this->assertTrue($this->svc()->approve($id, $this->adminId));
        $this->assertFalse($this->svc()->approve($id, $this->adminId));
        $this->assertFalse($this->svc()->reject($id, $this->adminId));
        $this->assertSame(100, $this->balance($user)); // the hold stays final
    }
}