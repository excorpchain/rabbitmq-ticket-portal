<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        Event::create([
            'title' => 'Концерт популярной группы',
            'description' => 'Большой музыкальный концерт',
            'venue' => 'Дворец спорта',
            'event_date' => '2026-12-15 19:00:00',
            'price' => 5000,
            'available_tickets' => 100,
        ]);

        Event::create([
            'title' => 'IT Conference 2026',
            'description' => 'Конференция по информационным технологиям',
            'venue' => 'Конгресс-холл',
            'event_date' => '2026-12-20 10:00:00',
            'price' => 7500,
            'available_tickets' => 80,
        ]);

        Event::create([
            'title' => 'Театральная премьера',
            'description' => 'Премьера нового спектакля',
            'venue' => 'Драматический театр',
            'event_date' => '2026-12-25 18:30:00',
            'price' => 3500,
            'available_tickets' => 50,
        ]);
    }
}
