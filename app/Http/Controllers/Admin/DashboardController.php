<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameCase;
use App\Models\LiveDrop;
use App\Models\Skin;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'users' => User::count(),
                'admins' => User::where('role', 'admin')->count(),
                'managers' => User::where('role', 'manager')->count(),
                'skins' => Skin::count(),
                'cases' => GameCase::count(),
                'opensToday' => LiveDrop::whereDate('created_at', today())->count(),
            ],
        ]);
    }
}
