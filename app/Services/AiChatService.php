<?php

namespace App\Services;

use Anthropic\Client;
use App\Models\KnowledgeBase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AiChatService
{
    public function __construct(private Client $anthropicClient) {}

    /**
     * Retrieve relevant knowledge base entries for a question.
     */
    public function retrieveRelevantKnowledge(string $question, int $limit = 3): Collection
    {
        $keywords = $this->extractKeywords($question);

        return KnowledgeBase::query()
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhereJsonContains('keywords', $keyword)
                        ->orWhere('title', 'like', "%{$keyword}%")
                        ->orWhere('content', 'like', "%{$keyword}%");
                }
            })
            ->limit($limit)
            ->get();
    }

    /**
     * Check if the question contains sensitive topics requiring escalation.
     */
    public function shouldEscalate(string $question): bool
    {
        $sensitiveKeywords = config('ai.sensitive_keywords', []);
        $questionLower = Str::lower($question);

        foreach ($sensitiveKeywords as $keyword) {
            if (Str::contains($questionLower, Str::lower($keyword))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a response using AI with retrieved knowledge.
     */
    public function generateResponse(string $question, Collection $knowledge): array
    {
        if ($this->shouldEscalate($question)) {
            return [
                'content' => $this->getEscalationMessage(),
                'confidence' => 1.0,
                'escalated' => true,
            ];
        }

        $context = $knowledge->map(function ($item) {
            return "Title: {$item->title}\nContent: {$item->content}";
        })->join("\n\n");

        $systemPrompt = $this->buildSystemPrompt();
        $userPrompt = $this->buildUserPrompt($question, $context);

        try {
            $message = $this->anthropicClient->messages->create(
                maxTokens: config('ai.anthropic.max_tokens', 1024),
                messages: [['role' => 'user', 'content' => $userPrompt]],
                model: config('ai.anthropic.model', 'claude-3-5-sonnet-20241022'),
                system: $systemPrompt,
            );

            $responseContent = $message->content[0]->text ?? '';
            $confidence = $this->calculateConfidence($responseContent, $knowledge);

            return [
                'content' => $responseContent,
                'confidence' => $confidence,
                'escalated' => false,
            ];
        } catch (\Exception $e) {
            return [
                'content' => 'I apologize, but I encountered an error. Please try again or speak with an operator.',
                'confidence' => 0.0,
                'escalated' => true,
            ];
        }
    }

    /**
     * Extract keywords from the question.
     */
    private function extractKeywords(string $question): array
    {
        $stopWords = ['what', 'when', 'where', 'who', 'how', 'is', 'are', 'the', 'a', 'an', 'do', 'does', 'can', 'could', 'would', 'your'];

        $words = Str::of($question)
            ->lower()
            ->replaceMatches('/[^a-z0-9\s]/', '')
            ->split('/\s+/')
            ->filter(fn ($word) => strlen($word) > 2)
            ->reject(fn ($word) => in_array($word, $stopWords))
            ->values()
            ->toArray();

        return $words;
    }

    /**
     * Build the system prompt for the AI.
     */
    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a helpful childcare center front desk assistant. Your role is to answer parent questions professionally and accurately based on the provided knowledge base.

Guidelines:
- Only answer based on the provided context
- Be friendly, professional, and concise
- If you don't have enough information, acknowledge it politely
- Never make up information
- Keep responses brief and to the point
PROMPT;
    }

    /**
     * Build the user prompt with context.
     */
    private function buildUserPrompt(string $question, string $context): string
    {
        return <<<PROMPT
Context from knowledge base:
{$context}

Parent question: {$question}

Please provide a helpful response based on the context above.
PROMPT;
    }

    /**
     * Calculate confidence score based on response and knowledge.
     */
    private function calculateConfidence(string $response, Collection $knowledge): float
    {
        if ($knowledge->isEmpty()) {
            return 0.3;
        }

        if (Str::contains(Str::lower($response), ['don\'t have', 'not sure', 'unclear', 'don\'t know'])) {
            return 0.4;
        }

        if ($knowledge->count() >= 2) {
            return 0.9;
        }

        return 0.7;
    }

    /**
     * Get escalation message.
     */
    private function getEscalationMessage(): string
    {
        return 'This question requires personal attention from our staff. An operator will assist you shortly.';
    }
}
