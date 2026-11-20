<?php

namespace App\Http\Controllers;

use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::query()
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->get();

        return view('events.index', compact('events'));
    }
}
