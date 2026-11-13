<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SkinController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Skins/Index', [
            'skins' => Skin::orderBy('name')->get()->map->toFrontend(),
            'rarities' => $this->rarities(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/Skins/Form', [
            'skin' => null,
            'rarities' => $this->rarities(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSkin($request);
        Skin::create($validated);

        return redirect()->route('admin.skins.index')->with('success', 'Скин добавлен в пул.');
    }

    public function edit(Skin $skin): Response
    {
        return Inertia::render('admin/Skins/Form', [
            'skin' => $skin->toFrontend(),
            'rarities' => $this->rarities(),
        ]);
    }

    public function update(Request $request, Skin $skin): RedirectResponse
    {
        $skin->update($this->validateSkin($request));

        return redirect()->route('admin.skins.index')->with('success', 'Скин обновлён.');
    }

    public function destroy(Skin $skin): RedirectResponse
    {
        $skin->delete();

        return back()->with('success', 'Скин удалён из пула.');
    }

    private function validateSkin(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'weapon' => ['required', 'string', 'max:255'],
            'rarity' => ['required', 'in:consumer,industrial,mil-spec,restricted,classified,covert,gold'],
            'price' => ['required', 'integer', 'min:1'],
            'image' => ['required', 'url', 'max:2048'],
        ]);
    }

    private function rarities(): array
    {
        return [
            ['value' => 'consumer', 'label' => 'Consumer Grade'],
            ['value' => 'industrial', 'label' => 'Industrial Grade'],
            ['value' => 'mil-spec', 'label' => 'Mil-Spec'],
            ['value' => 'restricted', 'label' => 'Restricted'],
            ['value' => 'classified', 'label' => 'Classified'],
            ['value' => 'covert', 'label' => 'Covert'],
            ['value' => 'gold', 'label' => '★ Rare Special'],
        ];
    }
}
