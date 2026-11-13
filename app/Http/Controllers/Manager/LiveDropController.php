<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LiveDrop;
use Inertia\Inertia;
use Inertia\Response;

class LiveDropController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('manager/LiveDrops/Index', [
            'drops' => LiveDrop::query()
                ->with(['user', 'skin', 'gameCase'])
                ->latest()
                ->limit(100)
                ->get()
                ->map->toFrontend(),
        ]);
    }
}
