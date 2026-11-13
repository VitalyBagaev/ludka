<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\GameCase;
use App\Models\Skin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CaseController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('manager/Cases/Index', [
            'cases' => GameCase::withCount('skins')->orderBy('name')->get()->map(fn (GameCase $gameCase) => [
                ...$gameCase->only(['id', 'name', 'price', 'category', 'featured', 'image']),
                'skinsCount' => $gameCase->skins_count,
            ]),
        ]);
    }

    public function edit(GameCase $gameCase): Response
    {
        $gameCase->load('skins');

        return Inertia::render('manager/Cases/Form', [
            'caseItem' => [
                ...$gameCase->only(['id', 'name', 'price', 'image', 'category', 'featured']),
                'skins' => $gameCase->skins->map(fn (Skin $skin) => [
                    ...$skin->toFrontend(),
                    'dropWeight' => $skin->pivot->drop_weight,
                ]),
            ],
            'allSkins' => Skin::orderBy('name')->get()->map->toFrontend(),
            'categories' => $this->categories(),
        ]);
    }

    public function update(Request $request, GameCase $gameCase): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:1'],
            'image' => ['required', 'url', 'max:2048'],
            'category' => ['required', 'in:popular,new,premium,budget'],
            'featured' => ['boolean'],
        ]);

        $gameCase->update($validated);
        $this->syncSkins($gameCase, $request->input('skins', []));

        return redirect()->route('manager.cases.index')->with('success', 'Кейс обновлён.');
    }

    public function toggleFeatured(GameCase $gameCase): RedirectResponse
    {
        $gameCase->update(['featured' => ! $gameCase->featured]);

        return back()->with('success', $gameCase->featured ? 'Кейс добавлен в Hot' : 'Кейс убран из Hot');
    }

    /**
     * @param  array<int, array{skin_id: int, drop_weight: int}>  $skins
     */
    private function syncSkins(GameCase $gameCase, array $skins): void
    {
        $sync = [];
        foreach ($skins as $item) {
            if (! empty($item['skin_id'])) {
                $sync[$item['skin_id']] = ['drop_weight' => $item['drop_weight'] ?? 100];
            }
        }
        $gameCase->skins()->sync($sync);
    }

    private function categories(): array
    {
        return [
            ['value' => 'popular', 'label' => 'Популярные'],
            ['value' => 'new', 'label' => 'Новые'],
            ['value' => 'premium', 'label' => 'Премиум'],
            ['value' => 'budget', 'label' => 'Бюджетные'],
        ];
    }
}
