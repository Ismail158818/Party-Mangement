<?php

namespace App\Http\Controllers\Api;

use App\Models\Comment;
use App\Models\Event;
use App\Http\Resources\CommentResource;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;  
use Illuminate\Http\Request;

class CommentApiController extends Controller
{
    use ApiResponse;  

    public function index(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|integer|exists:events,id', 
        ]);
        
        $comments = Comment::where('event_id', $validated['event_id'])->get();
        
        if ($comments->isEmpty()) {
            return $this->error('لا توجد تعليقات لهذا الحدث', 404); 
        } else {
            return $this->success(CommentResource::collection($comments));  
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|integer|exists:events,id',
            'text' => 'required|string|max:255',
        ]);
        $isJoined = Event::where('id', $event_id)
        ->whereHas('users', fn($q) => $q->where('user_id', auth()->id()))
        ->exists();
    
        if (!$isJoined) {
            return $this->error('يجب أن تكون منضمًا للحدث لتتمكن من إضافة تعليق', 403); 
        }

        $comment = Comment::create([
            'user_id' => auth()->user()->id, 
            'event_id' => $validated['event_id'], 
            'text' => $validated['text'],
        ]);

        return $this->success(new CommentResource($comment), 'تم إضافة التعليق بنجاح', 201);  
    }

    public function destroy(Request $request)   
    {
        $comment = Comment::find($request->comment_id);

        if (!$comment) {
            return $this->error('التعليق غير موجود', 404);  
        }

        if(auth()->id() == $comment->user_id || auth()->user()->is_admin) {
            $comment->delete();
            return $this->success(null, 'تم حذف التعليق بنجاح', 200); 
        } else {
            return $this->error('ليس لديك صلاحية لحذف هذا التعليق', 403);  
        }
    }
    

    public function update(Request $request)   
    {
        $validated = $request->validate([
            'comment_id' => 'required|integer',
            'text' => 'required|string|max:255',
        ]);
        $comment = Comment::find($request->comment_id);
        if (!$comment) {
            return $this->error('التعليق غير موجود', 404);  
        }

        if (auth()->user()->id != $comment->user_id) {
            return $this->error('ليس لديك صلاحية لتعديل هذا التعليق', 403);  
        }

        $comment->text = $request->text;
        $comment->save();

        return $this->success(new CommentResource($comment), 'تم تعديل التعليق بنجاح', 200);  
    }
}
