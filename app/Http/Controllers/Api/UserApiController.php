<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserApiController extends Controller
{
    

    public function toggleJoin(Request $request)
    {  
        if (auth()->user()->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Admins are not allowed to perform this action'
            ], 403);
        }
        $request->validate([
            'event_id' => 'required|integer|exists:events,id',
            'quantity_tickets' => 'required|integer|min:1'
        ]);
        $event = Event::findOrFail($request->event_id);
        if ($request->quantity_tickets > $event->tickets_limit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not enough tickets available'
            ], 400);
        }
        $invoice = Invoice::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->first();
        if (!$invoice) {
            $new_invoice = Invoice::create([
                'user_id' => auth()->id(),
                'event_id' => $event->id,
                'quantity_tickets' => $request->quantity_tickets,
                'total_amount' => $event->tickect_price * $request->quantity_tickets,
                'payment_status' => 'pending'
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Invoice created successfully',
                'invoice' => $new_invoice
            ], 200);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Invoice already exists',
            'invoice' => $invoice
        ], 400);
    }

    

    public function update_user(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'nullable|string|min:8|confirmed',
            'old_password' => 'nullable|string|min:8',
        ]);

        if (!Hash::check($validated['old_password'], auth()->user()->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Old password is incorrect'
            ], 400);
        }

        if (auth()->id() === $request->id) {
            $user = User::find($request->id);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            if ($request->name) $user->name = $request->name;
            if ($request->email) $user->email = $request->email;
            if ($request->password) $user->password = Hash::make($request->password);

            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully'
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized to update this user'
        ], 403);
    }

    public function destroy_user(Request $request)
{
    $id = $request->id;

    if (auth()->user()->is_admin || auth()->id() == $id) {
        $user = User::find($id);
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        $user->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully'
        ], 200);
    }

    return response()->json([ 
        'status' => 'error',
        'message' => 'Unauthorized to delete this user'
    ], 403);
}

    public function showuser(Request $request)
    {
        $id = $request->id;
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
        ], 200);
    }
    public function search(Request $request)
    {
        $info = $request->info;
        $users = User::where('name', 'LIKE', "%{$info}%")
            ->orWhere('email', 'LIKE', "%{$info}%")
            ->paginate(10);

        if ($users->total() > 0) {
            if(auth()->user()->is_admin)
            {
                return response()->json([
                    'status' => 'success',
                    'users' => $users
                ], 200);
            }
            else
            {
                return response()->json([
                    'status' => 'success',
                    'users' => $users->pluck('name')
                ], 403);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'No users found'
        ], 404);
    }

    public function users_event(Request $request)
{
    $validated = $request->validate([
        'event_id' => 'required|integer|exists:events,id',
        'user_id' => auth()->user()->is_admin ? 'required|integer|exists:users,id' : ''
    ]);

    $userId = auth()->user()->is_admin ? $validated['user_id'] : auth()->id();

    $users = User::where('id', $userId)
        ->whereHas('events', function ($query) use ($validated) {
            $query->where('event_id', $validated['event_id']);
        })->paginate(10);

    if ($users->total() > 0) {
        return response()->json([
            'status' => 'success',
            'users' => $users
        ], 200);
    }

    return response()->json([
        'status' => 'error',
        'message' => 'No users found'
    ], 404);
}

  
}
