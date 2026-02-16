# AI Front Desk for Early Education

A production-ready AI-powered front desk system for childcare centers, built with Laravel 12, Vue 3, and Claude AI. This prototype demonstrates how AI can reduce administrative workload by automatically answering common parent questions while maintaining trustworthiness and knowing when to escalate to human staff.

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![Tests](https://img.shields.io/badge/tests-53%20passing-brightgreen.svg)
![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)
![Vue](https://img.shields.io/badge/Vue-3-green.svg)

## 🎯 Problem Statement

School administrators spend hours daily answering repetitive questions via phone, email, and text—questions already answered in handbooks and policies. Parents want fast, accurate answers. Operators are busy and can't always respond in real time.

**This system bridges that gap.**

## ✨ Features

### Parent Experience (Front Desk)
- **Conversational Chat Interface** - Clean, mobile-friendly chat with personalized greetings
- **AI-Powered Responses** - Claude Haiku 4.5 with RAG pipeline for accurate, context-aware answers
- **Markdown Formatting** - Professional-looking responses with lists, bold text, and proper structure
- **Suggested Questions** - Guide parents to common topics
- **Confidence Scoring** - System knows when it's uncertain and says so
- **Smart Escalation** - Automatically detects sensitive topics (custody, abuse, billing disputes) and routes to staff
- **Session Continuity** - Conversations maintain context across multiple questions

### Operator Experience (Control Center)
- **Real-time Dashboard** - Monitor questions asked, escalation rate, auto-resolution percentage
- **Knowledge Base Manager** - Edit and organize 48+ pre-seeded knowledge entries across 11 categories
- **Conversation Monitor** - Review all parent interactions, see what worked and what didn't
- **Analytics Tracking** - Understand parent needs and system performance
- **Category Organization** - Hours, tuition, enrollment, health, meals, schedule, pickup, safety, classrooms, policies, general

### Technical Excellence
- **Relevance-Based Search** - Weighted scoring (keywords: 10pts, title: 5pts, content: 1pt) ensures best answers surface first
- **RAG Pipeline** - Retrieval-Augmented Generation with semantic keyword matching
- **53 Passing Tests** - Comprehensive test coverage with Pest 4
- **Production Ready** - Database transactions, proper validation, error handling, SQL injection protection
- **Modern Stack** - Laravel 12, Vue 3 Composition API, Inertia.js v2, Tailwind CSS v4, TypeScript

## 🚀 Quick Start

### Prerequisites
- PHP 8.4+
- MySQL 8.0+
- Node.js 18+
- Composer 2.x
- Anthropic API key ([get one here](https://console.anthropic.com/))

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/yourusername/brightwheel-ai-frontdesk.git
cd brightwheel-ai-frontdesk
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure your `.env` file**
```env
DB_CONNECTION=mysql
DB_DATABASE=brightwheel-ai-frontdesk
DB_USERNAME=your_username
DB_PASSWORD=your_password

ANTHROPIC_API_KEY=your_api_key_here
ANTHROPIC_MODEL=claude-haiku-4-5-20251001
```

5. **Run migrations and seed data**
```bash
php artisan migrate --seed
```

This creates:
- Test user account: `test@example.com` / `password`
- 48 knowledge base entries for "Little Oaks Preschool"

6. **Build frontend assets**
```bash
npm run build
```

7. **Start the development server**
```bash
php artisan serve
```

Visit `http://localhost:8000`

## 📱 Usage

### Parent Chat Interface
Navigate to `/chat` to experience the parent-facing chat interface:
1. Enter your name
2. Ask questions like:
   - "What are your hours?"
   - "My child has a fever, can they come in?"
   - "What is the tuition for infants?"
   - "Are you open on Veterans Day?"

### Operator Dashboard
1. Log in with `test@example.com` / `password`
2. Navigate to `/operator/dashboard` to:
   - View real-time analytics
   - Monitor recent conversations
   - Manage knowledge base entries
   - Review all parent interactions

## 🧪 Testing

Run the full test suite:
```bash
php artisan test
```

Run specific test groups:
```bash
# AI service tests
php artisan test --filter=AiChatServiceTest

# Chat API tests
php artisan test --filter=ChatControllerTest
```

All 53 tests include:
- RAG pipeline keyword extraction and retrieval
- Relevance-based ranking
- Escalation detection
- Confidence scoring
- API validation
- Database transactions

## 🏗️ Architecture

### Backend (Laravel 12)
- **Controllers**: RESTful API endpoints for chat and operator views
- **Services**: `AiChatService` handles RAG pipeline and Claude API integration
- **Models**: Eloquent models for Knowledge Base, Conversations, Messages, Analytics
- **Validation**: Form Request classes for type-safe validation
- **Database**: MySQL with proper indexing and foreign keys

### Frontend (Vue 3 + Inertia.js)
- **Chat Interface**: `resources/js/pages/Chat.vue` - Parent-facing chat
- **Operator Dashboard**: `resources/js/pages/operator/` - Control center views
- **Components**: Reusable UI components with TypeScript
- **Routing**: Laravel Wayfinder for type-safe route generation

### AI Pipeline
1. **Question received** → Extract keywords (stop words filtered)
2. **Retrieve knowledge** → Relevance-based search (JSON keywords weighted highest)
3. **Generate context** → Format retrieved knowledge for Claude
4. **Claude API call** → Generate natural language response
5. **Calculate confidence** → Based on knowledge availability
6. **Check escalation** → Detect sensitive topics
7. **Return response** → Markdown-formatted answer with metadata

## 📊 Database Schema

**Key tables:**
- `knowledge_base` - Policies, schedules, FAQs organized by category
- `conversations` - Parent chat sessions with status tracking
- `messages` - Individual messages with role (parent/assistant) and confidence scores
- `analytics_events` - Event logging for insights and improvement

## 🎨 Customization

### Adding Knowledge Base Entries
1. Log into operator dashboard
2. Navigate to "Knowledge Base"
3. Add entries with:
   - Title and content
   - Keywords for matching
   - Category assignment
   - Optional seasonal dates

### Adjusting Confidence Thresholds
Edit `config/ai.php`:
```php
'confidence' => [
    'high_threshold' => 0.8,
    'medium_threshold' => 0.5,
    'no_knowledge' => 0.3,      // 0-2 sources
    'single_knowledge' => 0.7,   // 1 source
    'multiple_knowledge' => 0.9, // 2+ sources
],
```

### Adding Sensitive Keywords
Edit `config/ai.php`:
```php
'sensitive_keywords' => [
    'specific child', 'billing dispute', 'complaint', 'custody',
    'abuse', 'neglect', 'staff issue', 'tour scheduling',
    // Add more...
],
```

## 🔒 Security

- SQL injection protection with escaped wildcards
- CSRF protection with meta tags
- Database transactions for atomicity
- Laravel Fortify authentication
- Session-based security
- Input validation on all endpoints

## 🚢 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate production app key
- [ ] Configure production database
- [ ] Add Anthropic API key
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed knowledge base: `php artisan db:seed --class=KnowledgeBaseSeeder`
- [ ] Build assets: `npm run build`
- [ ] Configure queue worker for background jobs
- [ ] Set up SSL certificate
- [ ] Configure caching (Redis recommended)

## 📈 Performance

- **Response Time**: < 2s average for AI responses
- **Relevance Ranking**: Database-level scoring for fast retrieval
- **Caching**: Supports Laravel cache for knowledge base queries
- **Queue Support**: Background job processing ready
- **Database Optimization**: Proper indexing on frequently queried fields

## 🤝 Contributing

This is a prototype/demo project. Feel free to fork and adapt for your needs!

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- Built with [Laravel 12](https://laravel.com)
- Powered by [Anthropic Claude](https://www.anthropic.com/claude)
- UI components from [Reka UI](https://reka-ui.com)
- Icons from [Lucide](https://lucide.dev)

## 📞 Support

For questions or issues, please open a GitHub issue.

---

**Built with ❤️ for early education centers**
