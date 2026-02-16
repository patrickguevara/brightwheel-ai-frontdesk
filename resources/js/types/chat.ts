export interface Message {
    id: string;
    role: 'parent' | 'assistant' | 'operator';
    content: string;
    confidence_score?: number;
    created_at: string;
}

export interface ChatResponse {
    message: Message;
    session_id: string;
}

export interface ConversationResponse {
    messages: Message[];
}
