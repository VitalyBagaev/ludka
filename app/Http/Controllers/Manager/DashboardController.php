<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\GameCase;
use App\Models\LiveDrop;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('manager/Dashboard', [
            'stats' => [
                'totalUsers' => User::where('role', 'user')->count(),
                'totalCases' => GameCase::count(),
                'featuredCases' => GameCase::where('featured', true)->count(),
                'opensToday' => LiveDrop::whereDate('created_at', today())->count(),
                'opensWeek' => LiveDrop::where('created_at', '>=', now()->subWeek())->count(),
                'totalBalance' => User::sum('balance'),
            ],
            'recentDrops' => LiveDrop::recent(10)->map->toFrontend(),
        ]);
    }
}
