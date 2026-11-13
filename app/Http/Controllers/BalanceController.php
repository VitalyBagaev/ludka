<?php

namespace App\Http\Controllers;

use App\Services\BalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function __construct(private BalanceService $balanceService) {}

    public function deposit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:1000000'],
        ]);

        $this->balanceService->deposit($request->user(), $validated['amount']);

        return back()->with('success', "Баланс пополнен на {$validated['amount']} ₽");
    }
}
