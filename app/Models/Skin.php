<?php

namespace App\Models;

use App\Support\GameImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skin extends Model
{
    protected $fillable = ['name', 'weapon', 'rarity', 'price', 'image'];

    public function toFrontend(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'weapon' => $this->weapon,
            'rarity' => $this->rarity,
            'price' => $this->price,
            'image' => GameImages::skin($this->image),
        ];
    }

    public function gameCases(): BelongsToMany
    {
        return $this->belongsToMany(GameCase::class, 'case_skin')
            ->withPivot('drop_weight');
    }
}
