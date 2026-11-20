<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'venue',
        'event_date',
        'price',
        'available_tickets',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'price' => 'decimal:2',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
