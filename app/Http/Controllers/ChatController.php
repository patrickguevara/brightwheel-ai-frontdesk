<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMessageRequest;
use App\Models\AnalyticsEvent;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\AiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function __construct(private AiChatService $aiChatService) {}

    public function sendMessage(SendMessageRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            // Get or create conversation
            if ($request->session_id) {
                $conversation = Conversation::where('session_id', $request->session_id)->firstOrFail();
            } else {
                $conversation = Conversation::create([
                    'session_id' => Str::uuid()->toString(),
                    'parent_name' => $request->parent_name,
                    'status' => 'active',
                ]);
            }

            // Store parent message
            $parentMessage = Message::create([
                'conversation_id' => $conversation->id,
                'role' => 'parent',
                'content' => $request->message,
            ]);

            // Log analytics event - question asked
            AnalyticsEvent::create([
                'conversation_id' => $conversation->id,
                'event_type' => 'question_asked',
                'category' => 'interaction',
                'metadata' => [
                    'message_id' => $parentMessage->id,
                ],
            ]);

            // Retrieve knowledge and generate response
            $knowledge = $this->aiChatService->retrieveRelevantKnowledge($request->message);
            $aiResponse = $this->aiChatService->generateResponse($request->message, $knowledge);

            // Store assistant message with confidence_score and source_references
            $assistantMessage = Message::create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $aiResponse['content'],
                'confidence_score' => $aiResponse['confidence'] ?? 0.5,
                'source_references' => $knowledge->pluck('id')->toArray(),
            ]);

            // Handle escalation
            $escalated = $aiResponse['escalated'] ?? false;
            if ($escalated) {
                $conversation->update(['status' => 'escalated']);

                AnalyticsEvent::create([
                    'conversation_id' => $conversation->id,
                    'event_type' => 'escalated',
                    'category' => 'escalation',
                    'metadata' => [
                        'message_id' => $assistantMessage->id,
                        'reason' => $aiResponse['escalation_reason'] ?? 'sensitive_topic',
                    ],
                ]);
            } else {
                AnalyticsEvent::create([
                    'conversation_id' => $conversation->id,
                    'event_type' => 'answer_given',
                    'category' => 'interaction',
                    'metadata' => [
                        'message_id' => $assistantMessage->id,
                        'confidence_score' => $aiResponse['confidence'] ?? 0.5,
                    ],
                ]);
            }

            return response()->json([
                'session_id' => $conversation->session_id,
                'message' => [
                    'id' => $assistantMessage->id,
                    'role' => $assistantMessage->role,
                    'content' => $assistantMessage->content,
                    'confidence_score' => $assistantMessage->confidence_score,
                    'should_escalate' => $escalated,
                    'created_at' => $assistantMessage->created_at,
                ],
            ]);
        });
    }

    public function getConversation(string $sessionId): JsonResponse
    {
        $conversation = Conversation::where('session_id', $sessionId)
            ->with(['messages' => fn ($query) => $query->orderBy('created_at')])
            ->firstOrFail();

        return response()->json([
            'session_id' => $conversation->session_id,
            'status' => $conversation->status,
            'messages' => $conversation->messages,
        ]);
    }
}
