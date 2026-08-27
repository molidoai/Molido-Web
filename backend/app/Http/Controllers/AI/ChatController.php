<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\AI\AIGateway;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function conversations(Request $request)
    {
        $user = $request->user();

        $list = AiConversation::where('organization_id', $user->organization_id)
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return response()->json($list);
    }

    public function storeConversation(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'mode' => 'nullable|in:answer,action',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $conversation = AiConversation::create([
            'organization_id' => $user->organization_id,
            'user_id' => $user->id,
            'customer_id' => $validated['customer_id'] ?? null,
            'title' => $validated['title'] ?? 'گفتگوی جدید',
            'mode' => $validated['mode'] ?? 'answer',
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'گفتگو ایجاد شد',
            'conversation' => $conversation,
        ], 201);
    }

    public function messages(Request $request, $id)
    {
        $user = $request->user();

        $conversation = AiConversation::where('organization_id', $user->organization_id)
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $messages = $conversation->messages()->orderBy('id')->get();

        return response()->json([
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    /**
     * Send a message and get AI response through Gateway + Safety.
     */
    public function send(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->hasPermission('ai.chat.use')) {
            return response()->json(['message' => 'دسترسی به چت‌بات ندارید'], 403);
        }

        $conversation = AiConversation::where('organization_id', $user->organization_id)
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string|max:10000',
        ]);

        // Store user message
        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        // Build context from last messages
        $history = $conversation->messages()
            ->orderBy('id')
            ->take(20)
            ->get()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        $systemPrompt = [
            'role' => 'system',
            'content' => 'You are MOLIDO AI assistant. Answer helpfully in the user language. Do not invent business data. If action is needed, explain and wait for confirmation.',
        ];

        $messages = array_merge([$systemPrompt], $history);

        $gateway = app(AIGateway::class);
        $result = $gateway->chat($messages, [
            'organization_id' => $user->organization_id,
            'user_id' => $user->id,
            'mode' => $conversation->mode,
            'agent' => 'general',
        ]);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['error'] ?? 'خطا در سرویس هوش مصنوعی',
                'code' => $result['code'] ?? 'error',
                'requires_approval' => $result['requires_approval'] ?? false,
            ], 422);
        }

        // Store assistant message
        $assistantMessage = AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $result['content'],
            'input_tokens' => $result['usage']['input_tokens'] ?? 0,
            'output_tokens' => $result['usage']['output_tokens'] ?? 0,
            'metadata' => ['model' => $result['model'] ?? null],
        ]);

        return response()->json([
            'message' => 'پاسخ دریافت شد',
            'reply' => $assistantMessage,
            'usage' => $result['usage'] ?? [],
        ]);
    }
}
