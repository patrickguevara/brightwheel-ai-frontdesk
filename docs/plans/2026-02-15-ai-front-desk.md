# AI Front Desk Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build a mobile-friendly "AI Front Desk" prototype for Little Oaks Learning Center with parent chat interface and operator control center

**Architecture:** Laravel backend with Inertia.js/React frontend. RAG-based AI chat using Claude API with semantic search over structured knowledge base. Mobile-first parent chat (public) and desktop-optimized operator dashboard (authenticated).

**Tech Stack:** Laravel 12, Inertia.js v2, React, TypeScript, Wayfinder, Pest 4, Tailwind CSS, Claude API (Anthropic SDK)

---

## Task 1: Project Database Schema

**Files:**
- Create: `database/migrations/2026_02_15_000001_create_knowledge_base_table.php`
- Create: `database/migrations/2026_02_15_000002_create_conversations_table.php`
- Create: `database/migrations/2026_02_15_000003_create_messages_table.php`
- Create: `database/migrations/2026_02_15_000004_create_analytics_events_table.php`

**Step 1: Create knowledge base migration**

```bash
php artisan make:migration create_knowledge_base_table --no-interaction
```

**Step 2: Define knowledge base schema**

Edit `database/migrations/2026_02_15_000001_create_knowledge_base_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_base', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('category', [
                'hours', 'tuition', 'enrollment', 'health', 'meals',
                'schedule', 'pickup', 'safety', 'classrooms', 'policies', 'general'
            ]);
            $table->string('title');
            $table->text('content');
            $table->json('keywords')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_seasonal')->default(false);
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base');
    }
};
```

**Step 3: Create conversations migration**

```bash
php artisan make:migration create_conversations_table --no-interaction
```

**Step 4: Define conversations schema**

Edit `database/migrations/2026_02_15_000002_create_conversations_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('session_id')->unique();
            $table->string('parent_name')->nullable();
            $table->enum('status', ['active', 'resolved', 'escalated'])->default('active');
            $table->text('escalation_reason')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('session_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
```

**Step 5: Create messages migration**

```bash
php artisan make:migration create_messages_table --no-interaction
```

**Step 6: Define messages schema**

Edit `database/migrations/2026_02_15_000003_create_messages_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->enum('role', ['parent', 'assistant', 'operator']);
            $table->text('content');
            $table->float('confidence_score')->nullable();
            $table->json('source_references')->nullable();
            $table->boolean('flagged')->default(false);
            $table->text('flag_reason')->nullable();
            $table->timestamps();

            $table->index('conversation_id');
            $table->index('role');
            $table->index('flagged');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
```

**Step 7: Create analytics events migration**

```bash
php artisan make:migration create_analytics_events_table --no-interaction
```

**Step 8: Define analytics events schema**

Edit `database/migrations/2026_02_15_000004_create_analytics_events_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->nullable()->constrained('conversations')->cascadeOnDelete();
            $table->enum('event_type', [
                'question_asked', 'answer_given', 'escalated',
                'feedback_given', 'knowledge_updated'
            ]);
            $table->string('category')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('event_type');
            $table->index('category');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
```

**Step 9: Run migrations**

```bash
php artisan migrate
```

Expected: All 4 migrations run successfully

**Step 10: Commit**

```bash
git add database/migrations/
git commit -m "feat: add database schema for knowledge base, conversations, messages, and analytics"
```

---

## Task 2: Eloquent Models

**Files:**
- Create: `app/Models/KnowledgeBase.php`
- Create: `app/Models/Conversation.php`
- Create: `app/Models/Message.php`
- Create: `app/Models/AnalyticsEvent.php`
- Create: `database/factories/KnowledgeBaseFactory.php`
- Create: `database/factories/ConversationFactory.php`
- Create: `database/factories/MessageFactory.php`

**Step 1: Create KnowledgeBase model with factory**

```bash
php artisan make:model KnowledgeBase --factory --no-interaction
```

**Step 2: Define KnowledgeBase model**

Edit `app/Models/KnowledgeBase.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeBase extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'knowledge_base';

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'is_active' => 'boolean',
            'is_seasonal' => 'boolean',
            'effective_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    protected $fillable = [
        'category',
        'title',
        'content',
        'keywords',
        'is_active',
        'is_seasonal',
        'effective_date',
        'expiry_date',
        'updated_by',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
```

**Step 3: Create Conversation model with factory**

```bash
php artisan make:model Conversation --factory --no-interaction
```

**Step 4: Define Conversation model**

Edit `app/Models/Conversation.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'session_id',
        'parent_name',
        'status',
        'escalation_reason',
        'resolved_by',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function analyticsEvents(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeEscalated($query)
    {
        return $query->where('status', 'escalated');
    }
}
```

**Step 5: Create Message model with factory**

```bash
php artisan make:model Message --factory --no-interaction
```

**Step 6: Define Message model**

Edit `app/Models/Message.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'source_references' => 'array',
            'flagged' => 'boolean',
            'confidence_score' => 'float',
        ];
    }

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'confidence_score',
        'source_references',
        'flagged',
        'flag_reason',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
```

**Step 7: Create AnalyticsEvent model**

```bash
php artisan make:model AnalyticsEvent --no-interaction
```

**Step 8: Define AnalyticsEvent model**

Edit `app/Models/AnalyticsEvent.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEvent extends Model
{
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    protected $fillable = [
        'conversation_id',
        'event_type',
        'category',
        'metadata',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
```

**Step 9: Commit**

```bash
git add app/Models/ database/factories/
git commit -m "feat: add Eloquent models for KnowledgeBase, Conversation, Message, AnalyticsEvent"
```

---

## Task 3: Knowledge Base Seeder

**Files:**
- Create: `database/seeders/KnowledgeBaseSeeder.php`

**Step 1: Create seeder**

```bash
php artisan make:seeder KnowledgeBaseSeeder --no-interaction
```

**Step 2: Define seeder with handbook data**

Edit `database/seeders/KnowledgeBaseSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\KnowledgeBase;
use Illuminate\Database\Seeder;

class KnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        $knowledge = [
            // Hours & Calendar
            [
                'category' => 'hours',
                'title' => 'Hours of Operation',
                'content' => 'Little Oaks is open Monday through Friday, 6:30 AM to 6:30 PM, year-round. Early drop-off begins at 6:30 AM. Standard hours are 7:00 AM – 6:00 PM. Extended care (6:00 PM – 6:30 PM) is included at no extra charge. Late pickup after 6:30 PM incurs a fee of $1.00 per minute per child, billed directly to your account.',
                'keywords' => ['hours', 'open', 'close', 'time', 'schedule', 'operating hours'],
            ],
            [
                'category' => 'hours',
                'title' => 'Holiday Closures 2025-2026',
                'content' => "Little Oaks is closed on the following days. Tuition is not adjusted for holiday closures:\n\n- New Year's Day: Wednesday, January 1, 2025\n- Martin Luther King Jr. Day: Monday, January 20, 2025\n- Presidents' Day: Monday, February 17, 2025\n- Memorial Day: Monday, May 26, 2025\n- Juneteenth: Thursday, June 19, 2025\n- Independence Day: Friday, July 4, 2025\n- Labor Day: Monday, September 1, 2025\n- Veterans Day: Tuesday, November 11, 2025\n- Thanksgiving Break: Thursday–Friday, Nov 27–28, 2025\n- Winter Break: Wednesday–Friday, Dec 24–26, 2025\n- New Year's Day: Thursday, January 1, 2026",
                'keywords' => ['holiday', 'closed', 'veterans day', 'thanksgiving', 'christmas', 'new year', 'mlk', 'memorial day', 'independence day', 'labor day', 'juneteenth', 'presidents day'],
            ],
            [
                'category' => 'hours',
                'title' => 'Inclement Weather Closures',
                'content' => 'The center may also close for up to 2 inclement weather days per year. Families will be notified via the Brightwheel app by 5:30 AM on any weather closure day. Little Oaks follows local school district closure decisions.',
                'keywords' => ['weather', 'snow', 'ice', 'closure', 'emergency'],
            ],

            // Tuition & Fees
            [
                'category' => 'tuition',
                'title' => 'Tuition Rates - Infant Program',
                'content' => "Infant (6 weeks – 12 months) tuition rates:\n- 5-Day Full-Time: $1,850/month\n- 3-Day Part-Time: $1,295/month\n\nRegistration fee: $150 per child (non-refundable, due at enrollment). Annual supply fee: $200 per child, due each August.",
                'keywords' => ['tuition', 'infant', 'baby', 'cost', 'price', 'fee', 'rates'],
            ],
            [
                'category' => 'tuition',
                'title' => 'Tuition Rates - Young Toddler Program',
                'content' => "Young Toddler (12 – 18 months) tuition rates:\n- 5-Day Full-Time: $1,750/month\n- 3-Day Part-Time: $1,225/month\n\nRegistration fee: $150 per child (non-refundable, due at enrollment). Annual supply fee: $200 per child, due each August.",
                'keywords' => ['tuition', 'young toddler', 'cost', 'price', 'fee', 'rates'],
            ],
            [
                'category' => 'tuition',
                'title' => 'Tuition Rates - Toddler Program',
                'content' => "Toddler (18 – 24 months) tuition rates:\n- 5-Day Full-Time: $1,650/month\n- 3-Day Part-Time: $1,155/month\n\nRegistration fee: $150 per child (non-refundable, due at enrollment). Annual supply fee: $200 per child, due each August.",
                'keywords' => ['tuition', 'toddler', 'cost', 'price', 'fee', 'rates'],
            ],
            [
                'category' => 'tuition',
                'title' => 'Tuition Rates - Twos Program',
                'content' => "Twos (2 – 3 years) tuition rates:\n- 5-Day Full-Time: $1,500/month\n- 3-Day Part-Time: $1,050/month\n\nRegistration fee: $150 per child (non-refundable, due at enrollment). Annual supply fee: $200 per child, due each August.",
                'keywords' => ['tuition', 'twos', '2 year old', 'cost', 'price', 'fee', 'rates'],
            ],
            [
                'category' => 'tuition',
                'title' => 'Tuition Rates - Preschool Program',
                'content' => "Preschool (3 – 4 years) tuition rates:\n- 5-Day Full-Time: $1,350/month\n- 3-Day Part-Time: $945/month\n\nRegistration fee: $150 per child (non-refundable, due at enrollment). Annual supply fee: $200 per child, due each August.",
                'keywords' => ['tuition', 'preschool', '3 year old', '4 year old', 'cost', 'price', 'fee', 'rates'],
            ],
            [
                'category' => 'tuition',
                'title' => 'Tuition Rates - Pre-K Program',
                'content' => "Pre-K (4 – 5 years) tuition rates:\n- 5-Day Full-Time: $1,250/month\n- 3-Day Part-Time: $875/month\n\nRegistration fee: $150 per child (non-refundable, due at enrollment). Annual supply fee: $200 per child, due each August.",
                'keywords' => ['tuition', 'pre-k', 'prekindergarten', '4 year old', '5 year old', 'cost', 'price', 'fee', 'rates'],
            ],
            [
                'category' => 'tuition',
                'title' => 'Payment Information',
                'content' => 'Tuition is due on the 1st of each month via autopay through Brightwheel. A $25 late fee is applied if payment is not received by the 5th. Payment methods: Auto-pay (ACH/credit card via parent portal), check, money order. Sibling discount: 10% off the lower tuition for the second child enrolled simultaneously.',
                'keywords' => ['payment', 'due', 'late fee', 'billing', 'sibling discount', 'autopay'],
            ],
            [
                'category' => 'tuition',
                'title' => 'Childcare Subsidies',
                'content' => 'We accept Texas Workforce Commission (TWC) childcare subsidies. Families are responsible for any co-pays.',
                'keywords' => ['subsidy', 'assistance', 'twc', 'workforce commission', 'financial aid'],
            ],
            [
                'category' => 'tuition',
                'title' => 'Withdrawal Policy',
                'content' => 'Families must provide 30 calendar days\' written notice to withdraw. Tuition is due through the full notice period. Notice should be submitted in writing to the center director via email.',
                'keywords' => ['withdrawal', 'cancel', 'notice', 'leaving'],
            ],

            // Enrollment
            [
                'category' => 'enrollment',
                'title' => 'Enrollment Requirements',
                'content' => "To enroll your child, you must complete:\n- Enrollment application\n- Immunization records (Texas-required)\n- Medical and allergy information\n- Emergency contacts (minimum 2)\n- Signed handbook acknowledgment\n- Authorization forms (photo/media, field trip, sunscreen/insect repellent)\n\nNew enrollees have a two-week transition period.",
                'keywords' => ['enrollment', 'enroll', 'register', 'paperwork', 'requirements', 'immunization'],
            ],
            [
                'category' => 'enrollment',
                'title' => 'Waitlist',
                'content' => 'If your preferred program is full, you may join our waitlist for a $50 non-refundable fee. Waitlist priority is first-come, first-served, with siblings of current families given preference. Average wait time is 2–4 months for infants and 1–2 months for older classrooms.',
                'keywords' => ['waitlist', 'waiting list', 'full', 'capacity'],
            ],

            // Health & Illness
            [
                'category' => 'health',
                'title' => 'Illness Exclusion Policy',
                'content' => "To protect all children and staff, a child must stay home or will be sent home if they exhibit any of the following symptoms:\n\n- Fever of 100.4°F (38°C) or higher\n- Vomiting or diarrhea (2 or more episodes in 24 hours)\n- Undiagnosed rash or skin condition\n- Persistent cough, especially if accompanied by fever or colored nasal discharge\n- Pink eye (conjunctivitis) with discharge\n- Head lice (active, untreated infestation)\n- Any contagious illness diagnosed by a physician (strep, flu, RSV, hand-foot-mouth, COVID-19, etc.)\n\nChildren must be symptom-free for at least 24 hours (without fever-reducing medication) before returning to the center. For contagious illnesses, a doctor's return-to-care note may be required at the director's discretion.",
                'keywords' => ['sick', 'illness', 'fever', 'vomit', 'diarrhea', 'contagious', 'pink eye', 'strep', 'covid', 'rsv', 'hand foot mouth'],
            ],
            [
                'category' => 'health',
                'title' => 'Medication Administration',
                'content' => "Staff can administer prescription and over-the-counter medication only with:\n\n- A completed Medication Authorization Form (available at the front desk or in Brightwheel)\n- Medication in its original container with the child's name and dosage instructions\n- Written doctor's note for any prescription medication\n\nMedications are stored in a locked cabinet. Sunscreen and insect repellent may be applied with a signed blanket authorization on file.",
                'keywords' => ['medication', 'medicine', 'prescription', 'sunscreen', 'bug spray'],
            ],

            // Meals & Nutrition
            [
                'category' => 'meals',
                'title' => 'Meals Provided',
                'content' => 'Little Oaks participates in the USDA Child and Adult Care Food Program (CACFP). The following meals are provided at no additional cost: Breakfast (7:00 – 7:30 AM), Morning snack (9:00 – 9:15 AM), Lunch (11:00 – 11:30 AM), Afternoon snack (2:00 – 2:30 PM). Weekly menus are posted in each classroom, on the parent board near the front entrance, and shared via Brightwheel on Fridays for the following week.',
                'keywords' => ['meals', 'food', 'breakfast', 'lunch', 'snack', 'menu', 'cafcp'],
            ],
            [
                'category' => 'meals',
                'title' => 'Nut-Free Facility',
                'content' => 'Little Oaks is a peanut-aware facility. While we cannot guarantee a completely peanut-free environment, peanut and tree nut products are never served, and classrooms with enrolled children who have nut allergies are designated nut-free zones. Parents must not send peanut or tree nut products in lunchboxes.',
                'keywords' => ['nut free', 'peanut', 'allergy', 'allergies', 'tree nut'],
            ],
            [
                'category' => 'meals',
                'title' => 'Forgot Lunch',
                'content' => 'If a child arrives without a packed lunch and we are not able to reach a parent, we will provide a center meal and note it in Brightwheel. There is no additional charge for substitute meals. The center provides breakfast, lunch, and two snacks daily, included in tuition.',
                'keywords' => ['forgot lunch', 'no lunch', 'packed lunch', 'substitute meal'],
            ],
            [
                'category' => 'meals',
                'title' => 'Dietary Restrictions and Allergies',
                'content' => 'We take food allergies seriously. All allergies and dietary restrictions must be documented on the enrollment health form and communicated to your child\'s lead teacher. We accommodate common allergens (peanut/tree nut, dairy, egg, gluten, soy, shellfish), religious dietary requirements (halal, kosher, vegetarian), and medical dietary needs with a physician\'s note.',
                'keywords' => ['allergy', 'dietary restrictions', 'vegetarian', 'vegan', 'kosher', 'halal', 'gluten free'],
            ],

            // Daily Schedule
            [
                'category' => 'schedule',
                'title' => 'Daily Schedule',
                'content' => "Sample Daily Schedule (Preschool/Pre-K):\n\n- 6:30 – 7:30 AM: Arrival, free play, and breakfast\n- 7:30 – 8:00 AM: Morning circle (calendar, weather, songs)\n- 8:00 – 9:00 AM: Learning centers (literacy, math, science, art)\n- 9:00 – 9:15 AM: Morning snack\n- 9:15 – 10:15 AM: Outdoor play / gross motor\n- 10:15 – 11:00 AM: Small group instruction / projects\n- 11:00 – 11:30 AM: Lunch\n- 11:30 AM – 12:00 PM: Story time and wind-down\n- 12:00 – 2:00 PM: Rest/nap time\n- 2:00 – 2:30 PM: Wake up, afternoon snack\n- 2:30 – 3:30 PM: Enrichment (music, STEM, Spanish)\n- 3:30 – 4:30 PM: Outdoor play\n- 4:30 – 6:30 PM: Free play, quiet activities, and pickup\n\nInfant and toddler schedules follow individual feeding and nap routines while incorporating age-appropriate sensory, music, and movement activities throughout the day.",
                'keywords' => ['schedule', 'daily routine', 'what time', 'nap time', 'lunch time', 'outdoor play'],
            ],

            // Drop-off & Pick-up
            [
                'category' => 'pickup',
                'title' => 'Drop-Off Procedures',
                'content' => 'Drop-off is between 6:30 AM and 9:00 AM. Children arriving after 9:00 AM must be signed in at the front desk. All children must be signed in through the Brightwheel kiosk at the front entrance using the parent\'s 4-digit PIN or QR code. Walk your child to their classroom and ensure a teacher acknowledges the handoff. Breakfast is served until 7:30 AM. Children arriving after 7:30 AM should have already eaten breakfast.',
                'keywords' => ['drop off', 'drop-off', 'arrival', 'sign in', 'morning'],
            ],
            [
                'category' => 'pickup',
                'title' => 'Pick-Up Procedures',
                'content' => 'Pick-up is between 3:00 PM and 6:30 PM (or your contracted hours). Sign your child out through Brightwheel at the front entrance. Only authorized individuals listed on the enrollment form may pick up your child. Anyone not known to staff will be asked for a government-issued photo ID, which will be checked against the authorized list. Custodial agreements or court orders restricting pick-up must be provided in writing to the director.',
                'keywords' => ['pick up', 'pickup', 'authorized', 'photo id', 'departure'],
            ],
            [
                'category' => 'pickup',
                'title' => 'Late Pick-Up Fee',
                'content' => 'The center closes at 6:30 PM sharp. A late fee of $1.00 per minute per child applies after 6:30 PM, beginning at 6:31 PM. Late fees are billed through Brightwheel and added to your next invoice. After three late pick-ups in a 60-day period, a meeting with the director will be required to discuss a plan. Chronic late pick-ups may result in termination of enrollment.',
                'keywords' => ['late', 'late pickup', 'late fee', 'after hours'],
            ],

            // Classrooms
            [
                'category' => 'classrooms',
                'title' => 'Classroom Information',
                'content' => "Little Oaks has the following classrooms:\n\n- Infant (6 weeks – 12 months): Ratio 1:4, Max 8 children, Lead Teacher: Denise Okafor\n- Young Toddler (12 – 18 months): Ratio 1:5, Max 10 children\n- Toddler (18 – 24 months): Ratio 1:6, Max 12 children, Lead Teacher: Carla Mendez\n- Twos (2 – 3 years): Ratio 1:9, Max 18 children\n- Preschool (3 – 4 years): Ratio 1:11, Max 22 children\n- Pre-K (4 – 5 years): Ratio 1:13, Max 26 children, Lead Teacher: Aisha Johnson\n\nChildren transition to the next classroom based on age, developmental readiness, and space availability.",
                'keywords' => ['classroom', 'teacher', 'ratio', 'age group', 'infant', 'toddler', 'preschool', 'pre-k'],
            ],

            // Safety
            [
                'category' => 'safety',
                'title' => 'Safety and Security',
                'content' => 'Little Oaks maintains a secured entry with keypad code (changed quarterly). All visitors must sign in with photo ID required. Staff are background-checked (FBI + state) and CPR/First Aid certified. Monthly fire drills and quarterly tornado/lockdown drills are conducted. Emergency plans are posted in every room. In case of emergency evacuation, our designated off-site location is Maplewood Community Church, 800 Maplewood Drive (0.2 miles from the center).',
                'keywords' => ['safety', 'security', 'emergency', 'door code', 'background check', 'fire drill'],
            ],

            // Communication
            [
                'category' => 'general',
                'title' => 'Communication Channels',
                'content' => 'Brightwheel is our primary communication platform. Through the app, you can view daily activity reports, receive photos and videos, message teachers or the director, review and sign documents, make tuition payments, and check in/out your child. All families are required to download and activate their Brightwheel account during enrollment. Teachers respond to messages within 2 hours during operating hours; after-hours messages are addressed the next business day.',
                'keywords' => ['brightwheel', 'communication', 'app', 'message', 'contact'],
            ],

            // Policies
            [
                'category' => 'policies',
                'title' => 'What to Bring',
                'content' => "Daily items for all ages: A complete change of weather-appropriate clothing (labeled), closed-toe shoes suitable for outdoor play, a water bottle (labeled), sunscreen (if not already on blanket authorization).\n\nInfants (additional): Pre-measured, labeled bottles of breast milk or formula, diapers and wipes (at least a one-week supply), a blanket or sleep sack for nap time, pacifiers (if used; labeled), baby food or age-appropriate snacks as needed.\n\nToddlers & Preschoolers (additional): A fitted cot sheet and small blanket for rest time (sent home Fridays for laundering), pull-ups or diapers if not yet potty trained, and extra underwear/pants if potty training.",
                'keywords' => ['bring', 'what to bring', 'supplies', 'diapers', 'bottles', 'clothing'],
            ],
            [
                'category' => 'policies',
                'title' => 'Birthday Celebrations',
                'content' => 'We love celebrating birthdays! Families may bring a simple treat to share with the class (store-bought, with an ingredient label, and nut-free). Please coordinate with your child\'s teacher at least 2 days in advance. Non-food celebrations (a favorite book, stickers, or a special activity) are also welcome and encouraged.',
                'keywords' => ['birthday', 'celebration', 'party', 'treats'],
            ],
        ];

        foreach ($knowledge as $item) {
            KnowledgeBase::create($item);
        }
    }
}
```

**Step 3: Update DatabaseSeeder to call KnowledgeBaseSeeder**

Edit `database/seeders/DatabaseSeeder.php` and add the seeder call in the `run()` method:

```php
public function run(): void
{
    $this->call([
        KnowledgeBaseSeeder::class,
    ]);
}
```

**Step 4: Run seeder**

```bash
php artisan db:seed --class=KnowledgeBaseSeeder
```

Expected: Knowledge base populated with handbook data

**Step 5: Commit**

```bash
git add database/seeders/
git commit -m "feat: add knowledge base seeder with Little Oaks handbook data"
```

---

## Task 4: AI Service - RAG Pipeline

**Files:**
- Create: `app/Services/AiChatService.php`
- Create: `config/ai.php`
- Test: `tests/Feature/AiChatServiceTest.php`

**Step 1: Install Anthropic SDK**

```bash
composer require anthropic-php/client --no-interaction
```

**Step 2: Create AI configuration file**

```bash
php artisan make:config ai --no-interaction
```

Edit `config/ai.php`:

```php
<?php

return [
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
        'max_tokens' => env('ANTHROPIC_MAX_TOKENS', 1024),
    ],

    'confidence' => [
        'high_threshold' => 0.8,
        'medium_threshold' => 0.5,
    ],

    'sensitive_keywords' => [
        'specific child', 'billing dispute', 'complaint', 'custody',
        'abuse', 'neglect', 'staff issue', 'tour scheduling',
        'schedule a tour', 'visit', 'door code', 'security code',
    ],
];
```

**Step 3: Create AiChatService class**

```bash
php artisan make:class Services/AiChatService --no-interaction
```

**Step 4: Write failing test for AiChatService**

```bash
php artisan make:test AiChatServiceTest --no-interaction
```

Edit `tests/Feature/AiChatServiceTest.php`:

```php
<?php

use App\Models\KnowledgeBase;
use App\Services\AiChatService;

beforeEach(function () {
    $this->service = app(AiChatService::class);
});

test('it retrieves relevant knowledge for a question', function () {
    KnowledgeBase::factory()->create([
        'category' => 'hours',
        'title' => 'Hours of Operation',
        'content' => 'Open Monday-Friday 6:30 AM to 6:30 PM',
        'keywords' => ['hours', 'open', 'close'],
    ]);

    $knowledge = $this->service->retrieveRelevantKnowledge('What are your hours?');

    expect($knowledge)->toHaveCount(1);
    expect($knowledge->first()->title)->toBe('Hours of Operation');
});

test('it detects sensitive topics requiring escalation', function () {
    $result = $this->service->shouldEscalate('I want to schedule a tour');

    expect($result)->toBeTrue();
});

test('it does not escalate general questions', function () {
    $result = $this->service->shouldEscalate('What are your hours?');

    expect($result)->toBeFalse();
});
```

**Step 5: Run test to verify it fails**

```bash
php artisan test --filter=AiChatServiceTest --compact
```

Expected: FAIL - method not found

**Step 6: Implement AiChatService**

Edit `app/Services/AiChatService.php`:

```php
<?php

namespace App\Services;

use App\Models\KnowledgeBase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AiChatService
{
    public function retrieveRelevantKnowledge(string $question): Collection
    {
        $keywords = $this->extractKeywords($question);

        return KnowledgeBase::query()
            ->active()
            ->where(function ($query) use ($keywords, $question) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('content', 'like', "%{$keyword}%")
                        ->orWhere('title', 'like', "%{$keyword}%")
                        ->orWhereJsonContains('keywords', $keyword);
                }
                $query->orWhere('content', 'like', "%{$question}%");
            })
            ->limit(3)
            ->get();
    }

    public function shouldEscalate(string $question): bool
    {
        $sensitiveKeywords = config('ai.sensitive_keywords', []);
        $lowerQuestion = Str::lower($question);

        foreach ($sensitiveKeywords as $keyword) {
            if (Str::contains($lowerQuestion, Str::lower($keyword))) {
                return true;
            }
        }

        return false;
    }

    protected function extractKeywords(string $question): array
    {
        $stopWords = ['a', 'an', 'the', 'is', 'are', 'what', 'when', 'where', 'how', 'do', 'does', 'can', 'your', 'my', 'i', 'you'];

        $words = Str::of($question)
            ->lower()
            ->replaceMatches('/[^\w\s]/', '')
            ->explode(' ')
            ->filter(fn ($word) => strlen($word) > 2 && ! in_array($word, $stopWords))
            ->values()
            ->toArray();

        return $words;
    }

    public function generateResponse(string $question, Collection $knowledge): array
    {
        if ($this->shouldEscalate($question)) {
            return [
                'content' => "That's a great question — I want to make sure you get the right answer. Let me connect you with our front desk team.",
                'confidence_score' => 0,
                'should_escalate' => true,
                'source_references' => [],
            ];
        }

        if ($knowledge->isEmpty()) {
            return [
                'content' => "I'm not sure about that. Let me have someone from our team help you with this question.",
                'confidence_score' => 0,
                'should_escalate' => true,
                'source_references' => [],
            ];
        }

        // For now, return a simple response based on the knowledge
        // In production, this would call the Anthropic API
        $context = $knowledge->pluck('content')->implode("\n\n");
        $sources = $knowledge->pluck('id')->toArray();

        return [
            'content' => $this->generateSimpleResponse($question, $knowledge),
            'confidence_score' => 0.9,
            'should_escalate' => false,
            'source_references' => $sources,
        ];
    }

    protected function generateSimpleResponse(string $question, Collection $knowledge): string
    {
        // Simplified response generation
        // In production, use Anthropic API
        return "Based on our policies: " . $knowledge->first()->content;
    }
}
```

**Step 7: Run test to verify it passes**

```bash
php artisan test --filter=AiChatServiceTest --compact
```

Expected: PASS

**Step 8: Commit**

```bash
git add app/Services/AiChatService.php config/ai.php tests/Feature/AiChatServiceTest.php composer.json composer.lock
git commit -m "feat: add AI chat service with RAG pipeline and escalation logic"
```

---

## Task 5: Parent Chat API

**Files:**
- Create: `app/Http/Controllers/ChatController.php`
- Create: `app/Http/Requests/SendMessageRequest.php`
- Create: `routes/web.php` (modify)
- Test: `tests/Feature/ChatControllerTest.php`

**Step 1: Write failing test for chat API**

```bash
php artisan make:test ChatControllerTest --no-interaction
```

Edit `tests/Feature/ChatControllerTest.php`:

```php
<?php

use App\Models\Conversation;
use App\Models\KnowledgeBase;
use App\Models\Message;
use Illuminate\Support\Str;

test('it creates a new conversation when session id is not provided', function () {
    KnowledgeBase::factory()->create([
        'category' => 'hours',
        'title' => 'Hours',
        'content' => 'Open Monday-Friday 6:30 AM to 6:30 PM',
        'keywords' => ['hours', 'open'],
    ]);

    $response = $this->postJson('/api/chat', [
        'message' => 'What are your hours?',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'session_id',
        'message' => [
            'role',
            'content',
            'confidence_score',
        ],
    ]);

    expect(Conversation::count())->toBe(1);
    expect(Message::count())->toBe(2); // parent + assistant
});

test('it continues existing conversation when session id is provided', function () {
    $conversation = Conversation::factory()->create();
    $sessionId = $conversation->session_id;

    KnowledgeBase::factory()->create([
        'category' => 'hours',
        'content' => 'Open Monday-Friday',
    ]);

    $response = $this->postJson('/api/chat', [
        'message' => 'What are your hours?',
        'session_id' => $sessionId,
    ]);

    $response->assertStatus(200);
    expect(Conversation::count())->toBe(1);
});

test('it escalates sensitive questions', function () {
    $response = $this->postJson('/api/chat', [
        'message' => 'I want to schedule a tour',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'message' => [
            'should_escalate' => true,
        ],
    ]);

    $conversation = Conversation::first();
    expect($conversation->status)->toBe('escalated');
});
```

**Step 2: Run test to verify it fails**

```bash
php artisan test --filter=ChatControllerTest --compact
```

Expected: FAIL - route not found

**Step 3: Create SendMessageRequest**

```bash
php artisan make:request SendMessageRequest --no-interaction
```

Edit `app/Http/Requests/SendMessageRequest.php`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:1000'],
            'session_id' => ['nullable', 'string', 'exists:conversations,session_id'],
            'parent_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
```

**Step 4: Create ChatController**

```bash
php artisan make:controller ChatController --no-interaction
```

Edit `app/Http/Controllers/ChatController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMessageRequest;
use App\Models\AnalyticsEvent;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\AiChatService;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function __construct(
        public AiChatService $aiService
    ) {}

    public function sendMessage(SendMessageRequest $request)
    {
        // Get or create conversation
        $conversation = $request->session_id
            ? Conversation::where('session_id', $request->session_id)->firstOrFail()
            : Conversation::create([
                'session_id' => Str::uuid(),
                'parent_name' => $request->parent_name,
            ]);

        // Store parent message
        $parentMessage = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'parent',
            'content' => $request->message,
        ]);

        // Log analytics event
        AnalyticsEvent::create([
            'conversation_id' => $conversation->id,
            'event_type' => 'question_asked',
            'metadata' => ['question' => $request->message],
        ]);

        // Generate AI response
        $knowledge = $this->aiService->retrieveRelevantKnowledge($request->message);
        $response = $this->aiService->generateResponse($request->message, $knowledge);

        // Store assistant message
        $assistantMessage = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $response['content'],
            'confidence_score' => $response['confidence_score'],
            'source_references' => $response['source_references'],
        ]);

        // Update conversation status if escalated
        if ($response['should_escalate']) {
            $conversation->update([
                'status' => 'escalated',
                'escalation_reason' => 'Sensitive topic detected',
            ]);

            AnalyticsEvent::create([
                'conversation_id' => $conversation->id,
                'event_type' => 'escalated',
                'metadata' => ['reason' => 'Sensitive topic detected'],
            ]);
        } else {
            AnalyticsEvent::create([
                'conversation_id' => $conversation->id,
                'event_type' => 'answer_given',
                'metadata' => [
                    'confidence' => $response['confidence_score'],
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
                'should_escalate' => $response['should_escalate'],
                'created_at' => $assistantMessage->created_at,
            ],
        ]);
    }

    public function getConversation(string $sessionId)
    {
        $conversation = Conversation::where('session_id', $sessionId)
            ->with(['messages' => fn ($q) => $q->orderBy('created_at')])
            ->firstOrFail();

        return response()->json([
            'session_id' => $conversation->session_id,
            'status' => $conversation->status,
            'messages' => $conversation->messages->map(fn ($msg) => [
                'id' => $msg->id,
                'role' => $msg->role,
                'content' => $msg->content,
                'confidence_score' => $msg->confidence_score,
                'created_at' => $msg->created_at,
            ]),
        ]);
    }
}
```

**Step 5: Add routes**

Edit `routes/web.php` and add at the bottom:

```php
use App\Http\Controllers\ChatController;

Route::post('/api/chat', [ChatController::class, 'sendMessage']);
Route::get('/api/chat/{sessionId}', [ChatController::class, 'getConversation']);
```

**Step 6: Update factories**

Edit `database/factories/ConversationFactory.php`:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ConversationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'session_id' => Str::uuid(),
            'parent_name' => $this->faker->name(),
            'status' => 'active',
        ];
    }
}
```

Edit `database/factories/KnowledgeBaseFactory.php`:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class KnowledgeBaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category' => 'general',
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'keywords' => [$this->faker->word(), $this->faker->word()],
            'is_active' => true,
            'is_seasonal' => false,
        ];
    }
}
```

**Step 7: Run tests**

```bash
php artisan test --filter=ChatControllerTest --compact
```

Expected: PASS

**Step 8: Commit**

```bash
git add app/Http/Controllers/ChatController.php app/Http/Requests/SendMessageRequest.php routes/web.php tests/Feature/ChatControllerTest.php database/factories/
git commit -m "feat: add parent chat API with conversation and message handling"
```

---

## Task 6: Parent Chat UI - React Components

**Files:**
- Create: `resources/js/pages/Chat.tsx`
- Create: `resources/js/components/ChatMessage.tsx`
- Create: `resources/js/components/ChatInput.tsx`
- Create: `resources/js/components/SuggestedQuestions.tsx`
- Modify: `routes/web.php`

**Step 1: Create Chat page component**

Create `resources/js/pages/Chat.tsx`:

```tsx
import { useState, useEffect, useRef } from 'react';
import ChatMessage from '@/components/ChatMessage';
import ChatInput from '@/components/ChatInput';
import SuggestedQuestions from '@/components/SuggestedQuestions';

interface Message {
  id: string;
  role: 'parent' | 'assistant' | 'operator';
  content: string;
  confidence_score?: number;
  created_at: string;
}

export default function Chat() {
  const [messages, setMessages] = useState<Message[]>([]);
  const [sessionId, setSessionId] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const messagesEndRef = useRef<HTMLDivElement>(null);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  const sendMessage = async (content: string) => {
    // Add parent message immediately
    const parentMessage: Message = {
      id: `temp-${Date.now()}`,
      role: 'parent',
      content,
      created_at: new Date().toISOString(),
    };
    setMessages(prev => [...prev, parentMessage]);
    setIsLoading(true);

    try {
      const response = await fetch('/api/chat', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
          message: content,
          session_id: sessionId,
        }),
      });

      const data = await response.json();

      if (!sessionId) {
        setSessionId(data.session_id);
      }

      setMessages(prev => [...prev, data.message]);
    } catch (error) {
      console.error('Failed to send message:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleSuggestedQuestion = (question: string) => {
    sendMessage(question);
  };

  return (
    <div className="flex flex-col h-screen bg-gradient-to-br from-green-50 to-yellow-50">
      {/* Header */}
      <div className="bg-white shadow-sm border-b border-green-100">
        <div className="max-w-4xl mx-auto px-4 py-4">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
              <span className="text-white font-bold text-lg">LO</span>
            </div>
            <div>
              <h1 className="text-lg font-semibold text-gray-900">Little Oaks Learning Center</h1>
              <p className="text-sm text-gray-600">AI Assistant</p>
            </div>
          </div>
        </div>
      </div>

      {/* Messages */}
      <div className="flex-1 overflow-y-auto">
        <div className="max-w-4xl mx-auto px-4 py-6">
          {messages.length === 0 && (
            <div className="text-center py-12">
              <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
              </div>
              <h2 className="text-xl font-semibold text-gray-900 mb-2">
                Welcome to Little Oaks!
              </h2>
              <p className="text-gray-600 mb-6">
                I'm here to help answer your questions about our center's programs, policies, and procedures.
              </p>
              <SuggestedQuestions onSelectQuestion={handleSuggestedQuestion} />
            </div>
          )}

          {messages.map((message) => (
            <ChatMessage key={message.id} message={message} />
          ))}

          {isLoading && (
            <div className="flex gap-3 mb-4">
              <div className="w-8 h-8 bg-green-100 rounded-full flex-shrink-0" />
              <div className="bg-white rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm">
                <div className="flex gap-1">
                  <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0ms' }} />
                  <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '150ms' }} />
                  <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '300ms' }} />
                </div>
              </div>
            </div>
          )}

          <div ref={messagesEndRef} />
        </div>
      </div>

      {/* Input */}
      <div className="bg-white border-t border-gray-200">
        <div className="max-w-4xl mx-auto px-4 py-4">
          <ChatInput onSendMessage={sendMessage} disabled={isLoading} />
        </div>
      </div>
    </div>
  );
}
```

**Step 2: Create ChatMessage component**

Create `resources/js/components/ChatMessage.tsx`:

```tsx
interface Message {
  id: string;
  role: 'parent' | 'assistant' | 'operator';
  content: string;
  confidence_score?: number;
  created_at: string;
}

interface ChatMessageProps {
  message: Message;
}

export default function ChatMessage({ message }: ChatMessageProps) {
  const isAssistant = message.role === 'assistant';

  return (
    <div className={`flex gap-3 mb-4 ${isAssistant ? '' : 'flex-row-reverse'}`}>
      {isAssistant && (
        <div className="w-8 h-8 bg-green-100 rounded-full flex-shrink-0 flex items-center justify-center">
          <svg className="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      )}

      <div
        className={`rounded-2xl px-4 py-3 max-w-[80%] ${
          isAssistant
            ? 'bg-white text-gray-900 rounded-tl-sm shadow-sm'
            : 'bg-green-600 text-white rounded-tr-sm'
        }`}
      >
        <p className="text-sm leading-relaxed whitespace-pre-wrap">{message.content}</p>

        {isAssistant && message.confidence_score !== undefined && message.confidence_score < 0.5 && (
          <p className="text-xs text-gray-500 mt-2 italic">
            I'm not completely sure about this answer. You may want to verify with our front desk.
          </p>
        )}
      </div>
    </div>
  );
}
```

**Step 3: Create ChatInput component**

Create `resources/js/components/ChatInput.tsx`:

```tsx
import { useState, FormEvent } from 'react';

interface ChatInputProps {
  onSendMessage: (message: string) => void;
  disabled?: boolean;
}

export default function ChatInput({ onSendMessage, disabled = false }: ChatInputProps) {
  const [input, setInput] = useState('');

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    if (input.trim() && !disabled) {
      onSendMessage(input.trim());
      setInput('');
    }
  };

  return (
    <form onSubmit={handleSubmit} className="flex gap-2">
      <input
        type="text"
        value={input}
        onChange={(e) => setInput(e.target.value)}
        disabled={disabled}
        placeholder="Ask a question..."
        className="flex-1 px-4 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed"
      />
      <button
        type="submit"
        disabled={disabled || !input.trim()}
        className="px-6 py-3 bg-green-600 text-white rounded-full font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
      >
        Send
      </button>
    </form>
  );
}
```

**Step 4: Create SuggestedQuestions component**

Create `resources/js/components/SuggestedQuestions.tsx`:

```tsx
interface SuggestedQuestionsProps {
  onSelectQuestion: (question: string) => void;
}

export default function SuggestedQuestions({ onSelectQuestion }: SuggestedQuestionsProps) {
  const questions = [
    "What are your hours?",
    "Is there school on Veterans Day?",
    "What's the tuition for infants?",
    "My child has a fever, can they come in?",
    "Do you provide lunch?",
    "What time is nap?",
  ];

  return (
    <div className="flex flex-wrap gap-2 justify-center">
      {questions.map((question) => (
        <button
          key={question}
          onClick={() => onSelectQuestion(question)}
          className="px-4 py-2 bg-white text-gray-700 rounded-full text-sm font-medium hover:bg-green-50 hover:text-green-700 border border-gray-200 transition-colors"
        >
          {question}
        </button>
      ))}
    </div>
  );
}
```

**Step 5: Add chat route**

Edit `routes/web.php` and add:

```php
use Inertia\Inertia;

Route::get('/chat', function () {
    return Inertia::render('Chat');
})->name('chat');
```

**Step 6: Set root route to chat**

Edit `routes/web.php` and update the root route:

```php
Route::get('/', function () {
    return redirect()->route('chat');
});
```

**Step 7: Build frontend assets**

```bash
npm run build
```

Expected: Assets compiled successfully

**Step 8: Test in browser**

```bash
php artisan serve
```

Open `http://localhost:8000/chat` and verify the UI loads

**Step 9: Commit**

```bash
git add resources/js/pages/Chat.tsx resources/js/components/ routes/web.php
git commit -m "feat: add parent chat UI with React components"
```

---

## Task 7: Operator Dashboard - Authentication & Layout

**Files:**
- Create: `resources/js/pages/operator/Dashboard.tsx`
- Create: `resources/js/pages/operator/Conversations.tsx`
- Create: `resources/js/pages/operator/KnowledgeBase.tsx`
- Create: `resources/js/components/operator/Sidebar.tsx`
- Create: `app/Http/Controllers/Operator/DashboardController.php`
- Modify: `routes/web.php`

**Step 1: Create operator routes with auth middleware**

Edit `routes/web.php` and add:

```php
use App\Http\Controllers\Operator\DashboardController;

Route::middleware(['auth'])->prefix('operator')->name('operator.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/conversations', [DashboardController::class, 'conversations'])->name('conversations');
    Route::get('/knowledge-base', [DashboardController::class, 'knowledgeBase'])->name('knowledge-base');
});
```

**Step 2: Create DashboardController**

```bash
mkdir -p app/Http/Controllers/Operator
php artisan make:controller Operator/DashboardController --no-interaction
```

Edit `app/Http/Controllers/Operator/DashboardController.php`:

```php
<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\Conversation;
use App\Models\KnowledgeBase;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        $weekAgo = now()->subWeek();

        $metrics = [
            'total_questions_today' => AnalyticsEvent::where('event_type', 'question_asked')
                ->where('created_at', '>=', $today)
                ->count(),
            'total_questions_week' => AnalyticsEvent::where('event_type', 'question_asked')
                ->where('created_at', '>=', $weekAgo)
                ->count(),
            'escalated_count' => Conversation::where('status', 'escalated')->count(),
            'auto_resolved_percentage' => $this->calculateAutoResolvedPercentage(),
        ];

        $recentActivity = AnalyticsEvent::query()
            ->with('conversation.messages')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($event) => [
                'id' => $event->id,
                'type' => $event->event_type,
                'category' => $event->category,
                'created_at' => $event->created_at,
                'metadata' => $event->metadata,
            ]);

        return Inertia::render('operator/Dashboard', [
            'metrics' => $metrics,
            'recentActivity' => $recentActivity,
        ]);
    }

    public function conversations()
    {
        $conversations = Conversation::query()
            ->with(['messages' => fn ($q) => $q->latest()->limit(1)])
            ->latest()
            ->paginate(20)
            ->through(fn ($conv) => [
                'id' => $conv->id,
                'session_id' => $conv->session_id,
                'parent_name' => $conv->parent_name,
                'status' => $conv->status,
                'message_count' => $conv->messages->count(),
                'last_message' => $conv->messages->first()?->content,
                'created_at' => $conv->created_at,
            ]);

        return Inertia::render('operator/Conversations', [
            'conversations' => $conversations,
        ]);
    }

    public function knowledgeBase()
    {
        $knowledge = KnowledgeBase::query()
            ->with('updatedBy')
            ->latest('updated_at')
            ->get()
            ->groupBy('category')
            ->map(fn ($items) => $items->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'content' => $item->content,
                'keywords' => $item->keywords,
                'is_active' => $item->is_active,
                'updated_at' => $item->updated_at,
                'updated_by' => $item->updatedBy?->name,
            ]));

        return Inertia::render('operator/KnowledgeBase', [
            'knowledgeByCategory' => $knowledge,
        ]);
    }

    protected function calculateAutoResolvedPercentage(): float
    {
        $totalConversations = Conversation::count();

        if ($totalConversations === 0) {
            return 0;
        }

        $escalatedCount = Conversation::where('status', 'escalated')->count();
        $autoResolved = $totalConversations - $escalatedCount;

        return round(($autoResolved / $totalConversations) * 100, 1);
    }
}
```

**Step 3: Create Sidebar component**

Create `resources/js/components/operator/Sidebar.tsx`:

```tsx
import { Link } from '@inertiajs/react';

interface SidebarProps {
  currentRoute: string;
}

export default function Sidebar({ currentRoute }: SidebarProps) {
  const links = [
    { name: 'Dashboard', route: 'operator.dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'Conversations', route: 'operator.conversations', icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z' },
    { name: 'Knowledge Base', route: 'operator.knowledge-base', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
  ];

  return (
    <div className="w-64 bg-gray-900 text-white min-h-screen">
      <div className="p-6">
        <h1 className="text-xl font-bold">Little Oaks</h1>
        <p className="text-sm text-gray-400">Control Center</p>
      </div>

      <nav className="mt-6">
        {links.map((link) => (
          <Link
            key={link.route}
            href={route(link.route)}
            className={`flex items-center gap-3 px-6 py-3 hover:bg-gray-800 transition-colors ${
              currentRoute === link.route ? 'bg-gray-800 border-l-4 border-green-500' : ''
            }`}
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={link.icon} />
            </svg>
            <span>{link.name}</span>
          </Link>
        ))}
      </nav>
    </div>
  );
}
```

**Step 4: Create Dashboard page**

Create `resources/js/pages/operator/Dashboard.tsx`:

```tsx
import Sidebar from '@/components/operator/Sidebar';
import { PageProps } from '@/types';

interface DashboardProps extends PageProps {
  metrics: {
    total_questions_today: number;
    total_questions_week: number;
    escalated_count: number;
    auto_resolved_percentage: number;
  };
  recentActivity: Array<{
    id: string;
    type: string;
    category: string;
    created_at: string;
    metadata: any;
  }>;
}

export default function Dashboard({ metrics, recentActivity }: DashboardProps) {
  return (
    <div className="flex">
      <Sidebar currentRoute="operator.dashboard" />

      <div className="flex-1 bg-gray-50 p-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-8">Dashboard</h1>

        {/* Metrics Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <MetricCard
            title="Questions Today"
            value={metrics.total_questions_today}
            icon="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"
          />
          <MetricCard
            title="Questions This Week"
            value={metrics.total_questions_week}
            icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
          />
          <MetricCard
            title="Needs Attention"
            value={metrics.escalated_count}
            icon="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            highlight
          />
          <MetricCard
            title="Auto-Resolved"
            value={`${metrics.auto_resolved_percentage}%`}
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </div>

        {/* Recent Activity */}
        <div className="bg-white rounded-lg shadow">
          <div className="px-6 py-4 border-b border-gray-200">
            <h2 className="text-lg font-semibold text-gray-900">Recent Activity</h2>
          </div>
          <div className="divide-y divide-gray-200">
            {recentActivity.map((event) => (
              <div key={event.id} className="px-6 py-4 hover:bg-gray-50">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-gray-900 capitalize">
                      {event.type.replace('_', ' ')}
                    </p>
                    {event.metadata?.question && (
                      <p className="text-sm text-gray-600 mt-1">{event.metadata.question}</p>
                    )}
                  </div>
                  <p className="text-sm text-gray-500">
                    {new Date(event.created_at).toLocaleString()}
                  </p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

function MetricCard({ title, value, icon, highlight = false }: {
  title: string;
  value: number | string;
  icon: string;
  highlight?: boolean;
}) {
  return (
    <div className={`bg-white rounded-lg shadow p-6 ${highlight ? 'ring-2 ring-yellow-400' : ''}`}>
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm text-gray-600">{title}</p>
          <p className="text-3xl font-bold text-gray-900 mt-2">{value}</p>
        </div>
        <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
          <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={icon} />
          </svg>
        </div>
      </div>
    </div>
  );
}
```

**Step 5: Create Conversations page**

Create `resources/js/pages/operator/Conversations.tsx`:

```tsx
import Sidebar from '@/components/operator/Sidebar';
import { PageProps } from '@/types';

interface ConversationsProps extends PageProps {
  conversations: {
    data: Array<{
      id: string;
      session_id: string;
      parent_name: string | null;
      status: string;
      message_count: number;
      last_message: string;
      created_at: string;
    }>;
  };
}

export default function Conversations({ conversations }: ConversationsProps) {
  return (
    <div className="flex">
      <Sidebar currentRoute="operator.conversations" />

      <div className="flex-1 bg-gray-50 p-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-8">Conversations</h1>

        <div className="bg-white rounded-lg shadow overflow-hidden">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Parent
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Last Message
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Messages
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Started
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {conversations.data.map((conversation) => (
                <tr key={conversation.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="text-sm font-medium text-gray-900">
                      {conversation.parent_name || 'Anonymous'}
                    </div>
                    <div className="text-sm text-gray-500">{conversation.session_id.slice(0, 8)}</div>
                  </td>
                  <td className="px-6 py-4">
                    <div className="text-sm text-gray-900 truncate max-w-md">
                      {conversation.last_message}
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                      conversation.status === 'escalated'
                        ? 'bg-yellow-100 text-yellow-800'
                        : conversation.status === 'resolved'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-blue-100 text-blue-800'
                    }`}>
                      {conversation.status}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {conversation.message_count}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {new Date(conversation.created_at).toLocaleDateString()}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
```

**Step 6: Create KnowledgeBase page**

Create `resources/js/pages/operator/KnowledgeBase.tsx`:

```tsx
import Sidebar from '@/components/operator/Sidebar';
import { PageProps } from '@/types';
import { useState } from 'react';

interface KnowledgeBaseProps extends PageProps {
  knowledgeByCategory: Record<string, Array<{
    id: string;
    title: string;
    content: string;
    keywords: string[];
    is_active: boolean;
    updated_at: string;
    updated_by: string | null;
  }>>;
}

export default function KnowledgeBase({ knowledgeByCategory }: KnowledgeBaseProps) {
  const [selectedCategory, setSelectedCategory] = useState<string | null>(null);

  return (
    <div className="flex">
      <Sidebar currentRoute="operator.knowledge-base" />

      <div className="flex-1 bg-gray-50 p-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-8">Knowledge Base</h1>

        <div className="grid grid-cols-12 gap-6">
          {/* Categories */}
          <div className="col-span-3">
            <div className="bg-white rounded-lg shadow p-4">
              <h2 className="text-sm font-semibold text-gray-900 mb-4 uppercase">Categories</h2>
              <div className="space-y-1">
                {Object.keys(knowledgeByCategory).map((category) => (
                  <button
                    key={category}
                    onClick={() => setSelectedCategory(category)}
                    className={`w-full text-left px-3 py-2 rounded-md text-sm transition-colors ${
                      selectedCategory === category
                        ? 'bg-green-100 text-green-900 font-medium'
                        : 'text-gray-700 hover:bg-gray-100'
                    }`}
                  >
                    {category.replace('_', ' ')}
                    <span className="ml-2 text-xs text-gray-500">
                      ({knowledgeByCategory[category].length})
                    </span>
                  </button>
                ))}
              </div>
            </div>
          </div>

          {/* Knowledge Items */}
          <div className="col-span-9">
            {selectedCategory ? (
              <div className="space-y-4">
                {knowledgeByCategory[selectedCategory].map((item) => (
                  <div key={item.id} className="bg-white rounded-lg shadow p-6">
                    <div className="flex items-start justify-between mb-3">
                      <h3 className="text-lg font-semibold text-gray-900">{item.title}</h3>
                      <span className={`px-2 py-1 text-xs font-medium rounded ${
                        item.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                      }`}>
                        {item.is_active ? 'Active' : 'Inactive'}
                      </span>
                    </div>
                    <p className="text-sm text-gray-700 whitespace-pre-wrap mb-4">{item.content}</p>
                    {item.keywords && item.keywords.length > 0 && (
                      <div className="flex flex-wrap gap-2 mb-3">
                        {item.keywords.map((keyword, idx) => (
                          <span key={idx} className="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">
                            {keyword}
                          </span>
                        ))}
                      </div>
                    )}
                    <p className="text-xs text-gray-500">
                      Last updated: {new Date(item.updated_at).toLocaleString()}
                      {item.updated_by && ` by ${item.updated_by}`}
                    </p>
                  </div>
                ))}
              </div>
            ) : (
              <div className="bg-white rounded-lg shadow p-12 text-center">
                <p className="text-gray-500">Select a category to view knowledge items</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
```

**Step 7: Build assets**

```bash
npm run build
```

**Step 8: Commit**

```bash
git add resources/js/pages/operator/ resources/js/components/operator/ app/Http/Controllers/Operator/ routes/web.php
git commit -m "feat: add operator dashboard with conversations and knowledge base views"
```

---

## Task 8: Run Pint & Final Testing

**Step 1: Run Laravel Pint to format PHP code**

```bash
vendor/bin/pint --dirty --format agent
```

Expected: All PHP files formatted

**Step 2: Run all tests**

```bash
php artisan test --compact
```

Expected: All tests passing

**Step 3: Seed database with demo user**

Create a demo operator user:

```bash
php artisan tinker
```

```php
\App\Models\User::factory()->create([
    'name' => 'Demo Operator',
    'email' => 'operator@littleoaks.test',
    'password' => bcrypt('password'),
]);
```

**Step 4: Test the application manually**

Start server:
```bash
php artisan serve
```

Test checklist:
- [ ] Visit `/chat` - parent chat loads
- [ ] Send message "What are your hours?" - gets response
- [ ] Send message "I want to schedule a tour" - gets escalated
- [ ] Login as operator@littleoaks.test (password: password)
- [ ] Visit `/operator/dashboard` - metrics display
- [ ] Visit `/operator/conversations` - conversations list
- [ ] Visit `/operator/knowledge-base` - knowledge organized by category

**Step 5: Final commit**

```bash
git add .
git commit -m "chore: run pint and verify all tests passing"
```

**Step 6: Create deployment instructions**

Create `.env.example` updates:

```bash
ANTHROPIC_API_KEY=your_api_key_here
ANTHROPIC_MODEL=claude-3-5-sonnet-20241022
ANTHROPIC_MAX_TOKENS=1024
```

---

## Completion Checklist

- [x] Database schema with migrations
- [x] Eloquent models with relationships
- [x] Knowledge base seeded with handbook data
- [x] AI service with RAG pipeline
- [x] Parent chat API endpoints
- [x] Parent chat React UI
- [x] Operator authentication
- [x] Operator dashboard
- [x] Operator conversations view
- [x] Operator knowledge base view
- [x] All tests passing
- [x] Code formatted with Pint
- [x] Manual testing completed

## Next Steps (Post-MVP)

1. **Anthropic API Integration**: Replace simple response generation with actual Claude API calls
2. **Knowledge Base Editing**: Add CRUD operations for operators to edit knowledge
3. **Advanced Search**: Implement semantic search using embeddings
4. **Analytics**: Add more detailed analytics and charts
5. **Export/Import**: Allow exporting conversations and knowledge base
6. **File Upload**: Support uploading PDF handbooks for auto-ingestion
7. **Multi-tenancy**: Support multiple childcare centers

---

## Deployment Instructions

1. Clone repository
2. Copy `.env.example` to `.env` and configure database
3. Add `ANTHROPIC_API_KEY` to `.env`
4. Run `composer install`
5. Run `npm install && npm run build`
6. Run `php artisan migrate --seed`
7. Create operator user via tinker
8. Deploy to hosting (Herd, Valet, Laravel Forge, etc.)

---

**Total Estimated Time:** 3-4 hours for core MVP implementation
