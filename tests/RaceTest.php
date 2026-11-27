<?php
declare(strict_types=1);

final class RaceTest extends DbTestCase
{
    public function test_eight_simultaneous_claims_of_one_code_award_coins_once(): void
    {
        [$code] = $this->makeCodes();
        $users = [];
        for ($i = 0; $i < 8; $i++) {
            $users[] = $this->makeUser();
        }

        $start = microtime(true) + 3.0; // all workers wait for this moment, then fire together
        $running = [];
        foreach ($users as $userId) {
            $pipes = [];
            $proc = proc_open(
                [PHP_BINARY, __DIR__ . '/race_worker.php', (string) $userId, $code, (string) $start],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes
            );
            $running[] = [$proc, $pipes];
        }

        $outputs = [];
        foreach ($running as [$proc, $pipes]) {
            $outputs[] = trim(stream_get_contents($pipes[1]) . ' ' . stream_get_contents($pipes[2]));
            proc_close($proc);
        }

        $wins = count(array_filter($outputs, fn ($o) => str_starts_with($o, 'OK')));
        $this->assertSame(1, $wins, implode(' | ', $outputs));
        $this->assertSame(1, (int) $this->pdo->query("SELECT COUNT(*) FROM point_ledger WHERE entry_type = 'earn'")->fetchColumn());
        $this->assertSame(10, (int) $this->pdo->query('SELECT SUM(points) FROM point_ledger')->fetchColumn());
    }
}