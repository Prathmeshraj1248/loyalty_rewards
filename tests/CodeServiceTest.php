<?php
declare(strict_types=1);

use App\Services\CodeService;
use PHPUnit\Framework\TestCase;

final class CodeServiceTest extends TestCase
{
    private const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    public function test_generated_codes_are_well_formed(): void
    {
        $service = new CodeService();
        for ($i = 0; $i < 50; $i++) {
            $code = $service->normalize($service->generate());
            $this->assertSame(13, strlen($code));
            $this->assertTrue($service->isWellFormed($code));
        }
    }

    public function test_normalize_ignores_case_dashes_and_spaces(): void
    {
        $this->assertSame('K7QM3WPX9TRDH', (new CodeService())->normalize('k7qm 3wpx-9trd h'));
    }

    public function test_any_single_changed_character_fails_the_checksum(): void
    {
        $service = new CodeService();
        $code = $service->normalize($service->generate());
        for ($pos = 0; $pos < 12; $pos++) {
            $next = self::ALPHABET[(strpos(self::ALPHABET, $code[$pos]) + 1) % strlen(self::ALPHABET)];
            $this->assertFalse($service->isWellFormed(substr_replace($code, $next, $pos, 1)), "position $pos");
        }
    }

    public function test_swapped_neighbours_fail_the_checksum(): void
    {
        $service = new CodeService();
        for ($i = 0; $i < 20; $i++) {
            $code = $service->normalize($service->generate());
            if ($code[0] !== $code[1]) {
                $this->assertFalse($service->isWellFormed($code[1] . $code[0] . substr($code, 2)));
            }
        }
    }

    public function test_hash_is_stable_64_hex_and_differs_per_code(): void
    {
        $service = new CodeService();
        $this->assertSame($service->hash('ABC'), $service->hash('ABC'));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $service->hash('ABC'));
        $this->assertNotSame($service->hash('ABC'), $service->hash('ABD'));
    }
}