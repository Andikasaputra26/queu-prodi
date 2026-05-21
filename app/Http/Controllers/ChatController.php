<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Events\ChatMessageSent;

class ChatController extends Controller
{
    // --- PUBLIC ENDPOINTS ---

    public function startSession(Request $request)
    {
        $request->validate([
            'student_name' => 'required|string|max:255',
            'session_id' => 'required|string',
        ]);

        $session = ChatSession::firstOrCreate(
            ['session_id' => $request->session_id],
            [
                'student_name' => $request->student_name,
                'student_phone' => $request->student_phone ?? null,
                'status' => 'open',
                'last_activity_at' => now(),
            ]
        );

        if ($session->wasRecentlyCreated) {
            $session->messages()->create([
                'sender' => 'admin',
                'message' => "Halo {$session->student_name}! 👋\nAda yang bisa kami bantu terkait persuratan atau layanan di SI-PINTER?",
            ]);
        }

        return response()->json(['session' => $session]);
    }

    public function getMessages(Request $request, $sessionId)
    {
        $session = ChatSession::where('session_id', $sessionId)->firstOrFail();
        
        $reader = $request->query('reader', 'student'); 
        $targetSender = $reader === 'student' ? 'admin' : 'student';

        // Mark as read
        $session->messages()->where('sender', $targetSender)->where('is_read', false)->update(['is_read' => true]);

        $messages = $session->messages()->orderBy('created_at', 'asc')->get();
        return response()->json($messages);
    }

    public function sendMessage(Request $request, $sessionId)
    {
        $request->validate([
            'message' => 'required|string',
            'sender' => 'required|in:student,admin',
        ]);

        $session = ChatSession::where('session_id', $sessionId)->firstOrFail();

        if ($session->status === 'closed' && $request->sender === 'student') {
            return response()->json(['error' => 'Sesi telah ditutup.'], 403);
        }

        $message = $session->messages()->create([
            'sender' => $request->sender,
            'message' => $request->message,
        ]);

        if ($request->sender === 'admin') {
            $session->update(['last_activity_at' => now(), 'is_ai_active' => false]);
        } else {
            $session->update(['last_activity_at' => now()]);
            // If AI is active, dispatch job to generate AI reply
            if ($session->is_ai_active && env('GEMINI_API_KEY')) {
                \App\Jobs\ProcessGeminiChat::dispatch($session->id);
            }
        }

        // Broadcast event
        broadcast(new ChatMessageSent($message, $session));

        return response()->json($message);
    }

    // --- ADMIN ENDPOINTS ---

    public function index()
    {
        return view('admin.chat');
    }

    public function adminList()
    {
        $sessions = ChatSession::withCount(['messages as unread_count' => function ($query) {
            $query->where('sender', 'student')->where('is_read', false);
        }])->orderBy('last_activity_at', 'desc')->get();
        return response()->json($sessions);
    }

    public function closeSession($sessionId)
    {
        $session = ChatSession::where('session_id', $sessionId)->firstOrFail();
        
        // Broadcast a system message to let the student know it's closed BEFORE deleting
        // We broadcast a "fake" message object just for the websocket to catch it
        $fakeMessage = new ChatMessage([
            'sender' => 'admin',
            'message' => 'Sesi obrolan ini telah ditutup dan dihapus oleh Admin.',
            'created_at' => now(),
        ]);
        $fakeMessage->chat_session_id = $session->id;
        
        broadcast(new ChatMessageSent($fakeMessage, $session));

        // Actually delete the session from the database (messages cascade delete)
        $session->delete();

        return response()->json(['success' => true]);
    }

    public function toggleAi($sessionId)
    {
        $session = ChatSession::where('session_id', $sessionId)->firstOrFail();
        $session->is_ai_active = !$session->is_ai_active;
        $session->save();

        return response()->json([
            'success' => true,
            'is_ai_active' => (bool) $session->is_ai_active
        ]);
    }
}
