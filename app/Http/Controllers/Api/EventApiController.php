<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Http\Services\Fun_Event;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\EventResource;
use App\Traits\ApiResponse;

class EventApiController extends Controller
{
    use ApiResponse;
    public function index()
{
    $events = Event::paginate(10);

    if ($events->isEmpty()) {
        return $this->error('No events found', 404);
    }

    return $this->success(EventResource::collection($events), 'Events retrieved successfully');
}

public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'media' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov',
        'date' => 'required|date',
        'location' => 'required|string|max:255',
        'tickect_price' => 'required|integer',
        'tickets_limit' => 'required|integer'
    ]);

    

    if (Event::where('title', $validated['title'])->exists()) {
        return $this->error('Event with this title already exists', 409);
    }

    $store = new Fun_Event();
    $data = $store->store($validated);

    if (!$data) {
        return $this->error('Failed to create event', 500);
    }

    return $this->success(new EventResource($data), 'Event created successfully', 201);
}

    public function showevent(Request $request)
{
    $validated = $request->validate([
        'title' => 'nullable|string|max:255',
        'date' => 'nullable|date',
    ]);

    if (empty($validated['title']) && empty($validated['date'])) {
        return $this->error('Please provide at least one search criterion', 400);
    }

    $query = Event::query();

    if (!empty($validated['title'])) {
        $query->where('title', 'like', '%' . $validated['title'] . '%');
    }

    if (!empty($validated['date'])) {
        $query->whereDate('date', '=', $validated['date']);
    }

    $events = $query->get();

    if ($events->isEmpty()) {
        return $this->error('No results found', 404);
    }

    return $this->success(EventResource::collection($events), 'Events found');
}

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:events,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'ticket_price' => 'required|integer',
            'tickets_limit' => 'required|integer'
        ]);

        $event = Event::find($validated['id']);

        if (!$event) {
            return $this->error('Event not found', 404);
        }

        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'media' => $validated['media'] ?? $event->media,
            'date' => $validated['date'],
            'location' => $validated['location'],
            'ticket_price' => $validated['ticket_price'],
            'tickets_limit' => $validated['tickets_limit']
        ]);

        return $this->success(new EventResource($event), 'Event updated successfully');
    }

   
    public function destroy(Request $request)
    {
    $event = Event::find($request['id']);

    if (!$event) {
        return $this->error('Event not found', 404);
    }

    if ($event->users()->count() > 0) {
        $event->users()->detach(); 
    }
    $event->delete();
    return $this->success(null, 'Event deleted successfully');
    }
    

}
