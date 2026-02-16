<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\Conversation;
use App\Models\KnowledgeBase;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = Carbon::today();
        $weekAgo = Carbon::now()->subWeek();

        $totalQuestionsToday = AnalyticsEvent::query()
            ->where('event_type', 'question_asked')
            ->whereDate('created_at', '>=', $today)
            ->count();

        $totalQuestionsWeek = AnalyticsEvent::query()
            ->where('event_type', 'question_asked')
            ->whereDate('created_at', '>=', $weekAgo)
            ->count();

        $escalatedCount = Conversation::query()
            ->where('status', 'escalated')
            ->count();

        $totalConversations = Conversation::query()->count();
        $autoResolvedPercentage = $totalConversations > 0
            ? round((($totalConversations - $escalatedCount) / $totalConversations) * 100, 1)
            : 0;

        $recentActivity = AnalyticsEvent::query()
            ->with(['conversation.messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event_type' => $event->event_type,
                    'category' => $event->category,
                    'created_at' => $event->created_at,
                    'conversation' => $event->conversation ? [
                        'session_id' => $event->conversation->session_id,
                        'parent_name' => $event->conversation->parent_name,
                        'status' => $event->conversation->status,
                        'latest_message' => $event->conversation->messages->first()?->content,
                    ] : null,
                ];
            });

        return Inertia::render('operator/Dashboard', [
            'metrics' => [
                'total_questions_today' => $totalQuestionsToday,
                'total_questions_week' => $totalQuestionsWeek,
                'escalated_count' => $escalatedCount,
                'auto_resolved_percentage' => $autoResolvedPercentage,
            ],
            'recentActivity' => $recentActivity,
        ]);
    }

    public function conversations(): Response
    {
        $conversations = Conversation::query()
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->withCount('messages')
            ->latest()
            ->paginate(20)
            ->through(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'session_id' => $conversation->session_id,
                    'parent_name' => $conversation->parent_name,
                    'status' => $conversation->status,
                    'message_count' => $conversation->messages_count,
                    'last_message' => $conversation->messages->first()?->content,
                    'created_at' => $conversation->created_at,
                ];
            });

        return Inertia::render('operator/Conversations', [
            'conversations' => $conversations,
        ]);
    }

    public function knowledgeBase(): Response
    {
        $knowledge = KnowledgeBase::query()
            ->with('updatedBy')
            ->orderBy('category')
            ->orderBy('title')
            ->get()
            ->groupBy('category')
            ->map(function ($items) {
                return $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'content' => $item->content,
                        'keywords' => $item->keywords,
                        'is_active' => $item->is_active,
                        'updated_at' => $item->updated_at,
                        'updated_by_name' => $item->updatedBy?->name,
                    ];
                });
            });

        return Inertia::render('operator/KnowledgeBase', [
            'knowledgeByCategory' => $knowledge,
        ]);
    }
}
