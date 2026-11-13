<?php

namespace App\Services;

class PromoCodeService
{
    public const LEGENDARY_CODE = 'LUDOLEGEND';

    /** @var list<int> */
    private const BONUS_POOL = [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25];

    private const WINDOW_SECONDS = 300;

    public function getPublicPromos(): array
    {
        $window = $this->currentWindow();
        $expiresAt = ($window + 1) * self::WINDOW_SECONDS;

        return [
            'rotating' => [
                'code' => $this->codeForWindow($window),
                'bonusPercent' => $this->bonusForWindow($window),
                'expiresAt' => $expiresAt,
                'secondsLeft' => max(0, $expiresAt - time()),
            ],
        ];
    }

    public function getLegendaryPromo(): array
    {
        return [
            'code' => self::LEGENDARY_CODE,
            'bonusPercent' => 777,
            'label' => 'Легендарный бонус +777%',
        ];
    }

    public function resolve(?string $code): ?array
    {
        if (! $code) {
            return null;
        }

        $code = strtoupper(trim($code));

        if ($code === self::LEGENDARY_CODE) {
            return [
                'code' => $code,
                'bonusPercent' => 777,
                'type' => 'legendary',
            ];
        }

        $window = $this->currentWindow();
        if ($code === $this->codeForWindow($window)) {
            return [
                'code' => $code,
                'bonusPercent' => $this->bonusForWindow($window),
                'type' => 'rotating',
            ];
        }

        return null;
    }

    public function calculateCredit(int $amount, ?string $code): array
    {
        $promo = $this->resolve($code);
        $bonusPercent = $promo['bonusPercent'] ?? 0;
        $bonus = (int) round($amount * ($bonusPercent / 100));
        $total = $amount + $bonus;

        return [
            'amount' => $amount,
            'bonus' => $bonus,
            'total' => $total,
            'bonusPercent' => $bonusPercent,
            'promo' => $promo,
        ];
    }

    private function currentWindow(): int
    {
        return (int) floor(time() / self::WINDOW_SECONDS);
    }

    private function codeForWindow(int $window): string
    {
        return strtoupper(substr(hash('sha256', config('app.key').'promo'.$window), 0, 8));
    }

    private function bonusForWindow(int $window): int
    {
        $index = hexdec(substr(hash('sha256', config('app.key').'bonus'.$window), 0, 2)) % count(self::BONUS_POOL);

        return self::BONUS_POOL[$index];
    }
}
