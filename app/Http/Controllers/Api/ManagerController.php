<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameCase;
use App\Models\LiveDrop;
use App\Models\User;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function stats()
    {
        return [
            'totalUsers' => User::where('role', 'user')->count(),
            'totalCases' => GameCase::count(),
            'featuredCases' => GameCase::where('featured', true)->count(),
            'opensToday' => LiveDrop::whereDate('created_at', today())->count(),
            'totalBalance' => User::sum('balance'),
        ];
    }

    public function cases()
    {
        return GameCase::withCount('skins')
            ->where('category', '!=', 'secret')
            ->orderByDesc('price')
            ->orderBy('name')
            ->get();
    }

    public function toggleFeatured(GameCase $gameCase)
    {
        $gameCase->update(['featured' => ! $gameCase->featured]);

        return response()->json($gameCase->fresh());
    }

    public function liveDrops()
    {
        return LiveDrop::query()
            ->with(['user', 'skin', 'gameCase'])
            ->latest()
            ->limit(100)
            ->get()
            ->map->toFrontend();
    }
}
