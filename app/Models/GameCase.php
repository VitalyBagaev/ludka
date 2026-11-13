<?php

namespace App\Models;

use App\Support\GameEconomy;
use App\Support\GameImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameCase extends Model
{
    private const RARITY_ORDER = [
        'consumer' => 0,
        'industrial' => 1,
        'mil-spec' => 2,
        'restricted' => 3,
        'classified' => 4,
        'covert' => 5,
        'gold' => 6,
    ];

    protected $fillable = ['name', 'price', 'image', 'category', 'featured'];

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
        ];
    }

    public function skins(): BelongsToMany
    {
        return $this->belongsToMany(Skin::class, 'case_skin')
            ->withPivot('drop_weight');
    }

    public function liveDrops(): HasMany
    {
        return $this->hasMany(LiveDrop::class);
    }

    public function toFrontend(): array
    {
        $minPrice = GameEconomy::minSkinPriceForCase((int) $this->price, $this->category);

        $items = $this->skins
            ->when($minPrice > 0, fn ($skins) => $skins->filter(fn (Skin $skin) => (int) $skin->price >= $minPrice))
            ->sortBy(fn (Skin $skin) => self::RARITY_ORDER[$skin->rarity] ?? 99)
            ->values()
            ->map->toFrontend()
            ->all();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'image' => GameImages::case($this->image),
            'category' => $this->category,
            'featured' => $this->featured,
            'items' => $items,
        ];
    }
}
