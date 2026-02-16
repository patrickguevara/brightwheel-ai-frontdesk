# Knowledge Base CRUD Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Enable operators to create, edit, and delete knowledge base entries through modal-based UI

**Architecture:** RESTful KnowledgeBaseController with Form Request validation, Vue modal component for CRUD operations, Inertia.js for data handling

**Tech Stack:** Laravel 12, Vue 3 Composition API, Inertia.js v2, Tailwind CSS v4, Pest 4

---

## Task 1: Form Request Validation Classes

**Files:**
- Create: `app/Http/Requests/StoreKnowledgeBaseRequest.php`
- Create: `app/Http/Requests/UpdateKnowledgeBaseRequest.php`
- Test: `tests/Feature/KnowledgeBaseCrudTest.php`

**Step 1: Write failing test for create validation**

Create test file:

```php
<?php

use App\Models\User;

test('create requires category title and content', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/operator/knowledge-base', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['category', 'title', 'content']);
});

test('category must be valid enum value', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/operator/knowledge-base', [
        'category' => 'invalid-category',
        'title' => 'Test Title',
        'content' => 'Test content',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['category']);
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --filter=KnowledgeBaseCrudTest`
Expected: FAIL with "Route [POST /operator/knowledge-base] not defined"

**Step 3: Create StoreKnowledgeBaseRequest**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKnowledgeBaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in([
                'hours', 'tuition', 'enrollment', 'health', 'meals',
                'schedule', 'pickup', 'safety', 'classrooms', 'policies', 'general',
            ])],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string'],
            'is_active' => ['boolean'],
        ];
    }
}
```

**Step 4: Create UpdateKnowledgeBaseRequest**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKnowledgeBaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in([
                'hours', 'tuition', 'enrollment', 'health', 'meals',
                'schedule', 'pickup', 'safety', 'classrooms', 'policies', 'general',
            ])],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string'],
            'is_active' => ['boolean'],
        ];
    }
}
```

**Step 5: Commit**

```bash
git add app/Http/Requests/StoreKnowledgeBaseRequest.php app/Http/Requests/UpdateKnowledgeBaseRequest.php tests/Feature/KnowledgeBaseCrudTest.php
git commit -m "feat: add knowledge base form request validation"
```

---

## Task 2: Controller - Create Operation

**Files:**
- Create: `app/Http/Controllers/Operator/KnowledgeBaseController.php`
- Modify: `tests/Feature/KnowledgeBaseCrudTest.php`

**Step 1: Write failing test for create**

Add to `tests/Feature/KnowledgeBaseCrudTest.php`:

```php
test('authenticated user can create knowledge base entry', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/operator/knowledge-base', [
        'category' => 'hours',
        'title' => 'Test Entry',
        'content' => 'Test content here',
        'keywords' => ['test', 'keyword'],
        'is_active' => true,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('knowledge_base', [
        'category' => 'hours',
        'title' => 'Test Entry',
        'content' => 'Test content here',
        'updated_by' => $user->id,
    ]);
});

test('keywords are stored as array', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/operator/knowledge-base', [
        'category' => 'general',
        'title' => 'Keywords Test',
        'content' => 'Testing keywords',
        'keywords' => ['key1', 'key2', 'key3'],
    ]);

    $entry = \App\Models\KnowledgeBase::where('title', 'Keywords Test')->first();

    expect($entry->keywords)->toBe(['key1', 'key2', 'key3']);
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --filter=KnowledgeBaseCrudTest`
Expected: FAIL with "Route [POST /operator/knowledge-base] not defined"

**Step 3: Create KnowledgeBaseController with store method**

```php
<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKnowledgeBaseRequest;
use App\Models\KnowledgeBase;
use Illuminate\Http\JsonResponse;

class KnowledgeBaseController extends Controller
{
    public function store(StoreKnowledgeBaseRequest $request): JsonResponse
    {
        $entry = KnowledgeBase::create([
            ...$request->validated(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Knowledge base entry created successfully',
            'entry' => $entry,
        ]);
    }
}
```

**Step 4: Add route to web.php**

Modify `routes/web.php`, add inside the `operator` route group:

```php
Route::post('/knowledge-base', [App\Http\Controllers\Operator\KnowledgeBaseController::class, 'store'])->name('knowledge-base.store');
```

**Step 5: Run test to verify it passes**

Run: `php artisan test --filter=KnowledgeBaseCrudTest`
Expected: PASS

**Step 6: Commit**

```bash
git add app/Http/Controllers/Operator/KnowledgeBaseController.php routes/web.php tests/Feature/KnowledgeBaseCrudTest.php
git commit -m "feat: add knowledge base create endpoint"
```

---

## Task 3: Controller - Update Operation

**Files:**
- Modify: `app/Http/Controllers/Operator/KnowledgeBaseController.php`
- Modify: `tests/Feature/KnowledgeBaseCrudTest.php`

**Step 1: Write failing test for update**

Add to `tests/Feature/KnowledgeBaseCrudTest.php`:

```php
test('authenticated user can update knowledge base entry', function () {
    $user = User::factory()->create();
    $entry = \App\Models\KnowledgeBase::factory()->create([
        'title' => 'Original Title',
        'content' => 'Original content',
    ]);

    $response = $this->actingAs($user)->putJson("/operator/knowledge-base/{$entry->id}", [
        'category' => 'general',
        'title' => 'Updated Title',
        'content' => 'Updated content',
        'keywords' => ['updated'],
        'is_active' => false,
    ]);

    $response->assertStatus(200);

    $entry->refresh();

    expect($entry->title)->toBe('Updated Title')
        ->and($entry->content)->toBe('Updated content')
        ->and($entry->updated_by)->toBe($user->id)
        ->and($entry->is_active)->toBeFalse();
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --filter=KnowledgeBaseCrudTest`
Expected: FAIL with "Route [PUT /operator/knowledge-base/{id}] not defined"

**Step 3: Add update method to controller**

Add to `app/Http/Controllers/Operator/KnowledgeBaseController.php`:

```php
use App\Http\Requests\UpdateKnowledgeBaseRequest;

public function update(UpdateKnowledgeBaseRequest $request, KnowledgeBase $knowledgeBase): JsonResponse
{
    $knowledgeBase->update([
        ...$request->validated(),
        'updated_by' => auth()->id(),
    ]);

    return response()->json([
        'message' => 'Knowledge base entry updated successfully',
        'entry' => $knowledgeBase->fresh(),
    ]);
}
```

**Step 4: Add route to web.php**

Add to `operator` route group:

```php
Route::put('/knowledge-base/{knowledgeBase}', [App\Http\Controllers\Operator\KnowledgeBaseController::class, 'update'])->name('knowledge-base.update');
```

**Step 5: Run test to verify it passes**

Run: `php artisan test --filter=KnowledgeBaseCrudTest`
Expected: PASS

**Step 6: Commit**

```bash
git add app/Http/Controllers/Operator/KnowledgeBaseController.php routes/web.php tests/Feature/KnowledgeBaseCrudTest.php
git commit -m "feat: add knowledge base update endpoint"
```

---

## Task 4: Controller - Delete Operation

**Files:**
- Modify: `app/Http/Controllers/Operator/KnowledgeBaseController.php`
- Modify: `tests/Feature/KnowledgeBaseCrudTest.php`

**Step 1: Write failing test for delete**

Add to `tests/Feature/KnowledgeBaseCrudTest.php`:

```php
test('authenticated user can delete knowledge base entry', function () {
    $user = User::factory()->create();
    $entry = \App\Models\KnowledgeBase::factory()->create();

    $response = $this->actingAs($user)->deleteJson("/operator/knowledge-base/{$entry->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('knowledge_base', [
        'id' => $entry->id,
    ]);
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --filter=KnowledgeBaseCrudTest`
Expected: FAIL with "Route [DELETE /operator/knowledge-base/{id}] not defined"

**Step 3: Add destroy method to controller**

Add to `app/Http/Controllers/Operator/KnowledgeBaseController.php`:

```php
public function destroy(KnowledgeBase $knowledgeBase): JsonResponse
{
    $knowledgeBase->delete();

    return response()->json([
        'message' => 'Knowledge base entry deleted successfully',
    ]);
}
```

**Step 4: Add route to web.php**

Add to `operator` route group:

```php
Route::delete('/knowledge-base/{knowledgeBase}', [App\Http\Controllers\Operator\KnowledgeBaseController::class, 'destroy'])->name('knowledge-base.destroy');
```

**Step 5: Run test to verify it passes**

Run: `php artisan test --filter=KnowledgeBaseCrudTest`
Expected: PASS

**Step 6: Commit**

```bash
git add app/Http/Controllers/Operator/KnowledgeBaseController.php routes/web.php tests/Feature/KnowledgeBaseCrudTest.php
git commit -m "feat: add knowledge base delete endpoint"
```

---

## Task 5: Authentication Test

**Files:**
- Modify: `tests/Feature/KnowledgeBaseCrudTest.php`

**Step 1: Write failing test for unauthenticated access**

Add to `tests/Feature/KnowledgeBaseCrudTest.php`:

```php
test('unauthenticated user cannot access crud endpoints', function () {
    $entry = \App\Models\KnowledgeBase::factory()->create();

    $this->postJson('/operator/knowledge-base', [])->assertStatus(401);
    $this->putJson("/operator/knowledge-base/{$entry->id}", [])->assertStatus(401);
    $this->deleteJson("/operator/knowledge-base/{$entry->id}")->assertStatus(401);
});
```

**Step 2: Run test to verify it passes**

Run: `php artisan test --filter=KnowledgeBaseCrudTest`
Expected: PASS (auth middleware already applied to operator routes)

**Step 3: Commit**

```bash
git add tests/Feature/KnowledgeBaseCrudTest.php
git commit -m "test: verify authentication required for KB CRUD"
```

---

## Task 6: Knowledge Base Factory

**Files:**
- Create: `database/factories/KnowledgeBaseFactory.php`

**Step 1: Create factory**

Run: `php artisan make:factory KnowledgeBaseFactory --model=KnowledgeBase`

**Step 2: Implement factory**

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class KnowledgeBaseFactory extends Factory
{
    public function definition(): array
    {
        $categories = [
            'hours', 'tuition', 'enrollment', 'health', 'meals',
            'schedule', 'pickup', 'safety', 'classrooms', 'policies', 'general',
        ];

        return [
            'category' => fake()->randomElement($categories),
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'keywords' => [fake()->word(), fake()->word(), fake()->word()],
            'is_active' => true,
            'is_seasonal' => false,
            'effective_date' => null,
            'expiry_date' => null,
        ];
    }
}
```

**Step 3: Commit**

```bash
git add database/factories/KnowledgeBaseFactory.php
git commit -m "feat: add knowledge base factory for testing"
```

---

## Task 7: Modal Component Structure

**Files:**
- Create: `resources/js/components/operator/KnowledgeBaseModal.vue`

**Step 1: Create modal component with props and emits**

```vue
<script setup lang="ts">
import { ref, computed, watch } from 'vue';

interface KnowledgeEntry {
    id?: string;
    category: string;
    title: string;
    content: string;
    keywords: string[];
    is_active: boolean;
}

interface Props {
    show: boolean;
    entry?: KnowledgeEntry | null;
}

interface Emits {
    (e: 'close'): void;
    (e: 'saved'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const categories = [
    'hours', 'tuition', 'enrollment', 'health', 'meals',
    'schedule', 'pickup', 'safety', 'classrooms', 'policies', 'general',
];

const form = ref({
    category: '',
    title: '',
    content: '',
    keywords: '',
    is_active: true,
});

const errors = ref<Record<string, string>>({});
const loading = ref(false);

const isEditMode = computed(() => !!props.entry?.id);

// Reset form when modal opens/closes
watch(() => props.show, (show) => {
    if (show && props.entry) {
        form.value = {
            category: props.entry.category,
            title: props.entry.title,
            content: props.entry.content,
            keywords: props.entry.keywords.join('\n'),
            is_active: props.entry.is_active,
        };
    } else if (show) {
        form.value = {
            category: 'general',
            title: '',
            content: '',
            keywords: '',
            is_active: true,
        };
    }
    errors.value = {};
});

const close = () => {
    emit('close');
};
</script>

<template>
    <!-- Will add template in next step -->
    <div v-if="show">Modal Placeholder</div>
</template>
```

**Step 2: Commit**

```bash
git add resources/js/components/operator/KnowledgeBaseModal.vue
git commit -m "feat: add knowledge base modal component structure"
```

---

## Task 8: Modal Component - Form Template

**Files:**
- Modify: `resources/js/components/operator/KnowledgeBaseModal.vue`

**Step 1: Add template with form fields**

Replace the `<template>` section:

```vue
<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            @click="close"
        ></div>

        <!-- Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div
                class="relative w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl"
                @click.stop
            >
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ isEditMode ? 'Edit Knowledge Base Entry' : 'Create Knowledge Base Entry' }}
                    </h2>
                    <button
                        @click="close"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="save" class="space-y-4">
                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.category"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        >
                            <option v-for="cat in categories" :key="cat" :value="cat">
                                {{ cat.charAt(0).toUpperCase() + cat.slice(1) }}
                            </option>
                        </select>
                        <p v-if="errors.category" class="mt-1 text-sm text-red-600">
                            {{ errors.category }}
                        </p>
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            placeholder="Enter title"
                        />
                        <p v-if="errors.title" class="mt-1 text-sm text-red-600">
                            {{ errors.title }}
                        </p>
                    </div>

                    <!-- Content -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Content <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="form.content"
                            rows="8"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            placeholder="Enter content"
                        ></textarea>
                        <p v-if="errors.content" class="mt-1 text-sm text-red-600">
                            {{ errors.content }}
                        </p>
                    </div>

                    <!-- Keywords -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Keywords
                        </label>
                        <textarea
                            v-model="form.keywords"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            placeholder="Enter keywords, one per line"
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            Enter one keyword per line
                        </p>
                    </div>

                    <!-- Is Active Toggle -->
                    <div class="flex items-center">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500"
                        />
                        <label class="ml-2 block text-sm text-gray-700">
                            Active
                        </label>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-between pt-4">
                        <button
                            v-if="isEditMode"
                            type="button"
                            @click="deleteEntry"
                            :disabled="loading"
                            class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700 disabled:opacity-50"
                        >
                            Delete
                        </button>
                        <div v-else></div>

                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="close"
                                :disabled="loading"
                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="loading"
                                class="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700 disabled:opacity-50"
                            >
                                {{ loading ? 'Saving...' : 'Save' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
```

**Step 2: Commit**

```bash
git add resources/js/components/operator/KnowledgeBaseModal.vue
git commit -m "feat: add knowledge base modal form template"
```

---

## Task 9: Modal Component - Save Logic

**Files:**
- Modify: `resources/js/components/operator/KnowledgeBaseModal.vue`

**Step 1: Add save method to script**

Add to the `<script setup>` section:

```typescript
const save = async () => {
    loading.value = true;
    errors.value = {};

    // Convert keywords from textarea to array
    const keywords = form.value.keywords
        .split('\n')
        .map(k => k.trim())
        .filter(k => k.length > 0);

    const data = {
        category: form.value.category,
        title: form.value.title,
        content: form.value.content,
        keywords,
        is_active: form.value.is_active,
    };

    try {
        const url = isEditMode.value
            ? `/operator/knowledge-base/${props.entry!.id}`
            : '/operator/knowledge-base';

        const method = isEditMode.value ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(data),
        });

        if (!response.ok) {
            if (response.status === 422) {
                const errorData = await response.json();
                errors.value = errorData.errors || {};
                return;
            }
            throw new Error('Failed to save entry');
        }

        emit('saved');
        emit('close');
    } catch (error) {
        console.error('Error saving entry:', error);
        errors.value = { general: 'Failed to save entry. Please try again.' };
    } finally {
        loading.value = false;
    }
};
```

**Step 2: Commit**

```bash
git add resources/js/components/operator/KnowledgeBaseModal.vue
git commit -m "feat: add knowledge base modal save logic"
```

---

## Task 10: Modal Component - Delete Logic

**Files:**
- Modify: `resources/js/components/operator/KnowledgeBaseModal.vue`

**Step 1: Add deleteEntry method to script**

Add to the `<script setup>` section:

```typescript
const deleteEntry = async () => {
    if (!confirm('Are you sure you want to delete this entry? This cannot be undone.')) {
        return;
    }

    loading.value = true;

    try {
        const response = await fetch(`/operator/knowledge-base/${props.entry!.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to delete entry');
        }

        emit('saved');
        emit('close');
    } catch (error) {
        console.error('Error deleting entry:', error);
        errors.value = { general: 'Failed to delete entry. Please try again.' };
    } finally {
        loading.value = false;
    }
};
```

**Step 2: Commit**

```bash
git add resources/js/components/operator/KnowledgeBaseModal.vue
git commit -m "feat: add knowledge base modal delete logic"
```

---

## Task 11: Update KnowledgeBase.vue - Import Modal

**Files:**
- Modify: `resources/js/pages/operator/KnowledgeBase.vue`

**Step 1: Import modal and add state**

Update the `<script setup>` section:

```typescript
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Sidebar from '@/components/operator/Sidebar.vue';
import KnowledgeBaseModal from '@/components/operator/KnowledgeBaseModal.vue';

interface KnowledgeItem {
    id: string;
    title: string;
    content: string;
    keywords: string[];
    is_active: boolean;
    updated_at: string;
    updated_by_name: string | null;
    category: string;
}

interface Props {
    knowledgeByCategory: Record<string, KnowledgeItem[]>;
}

const props = defineProps<Props>();

const expandedCategories = ref<Record<string, boolean>>({});
const showModal = ref(false);
const editingEntry = ref<KnowledgeItem | null>(null);

const toggleCategory = (category: string) => {
    expandedCategories.value[category] = !expandedCategories.value[category];
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const openCreateModal = () => {
    editingEntry.value = null;
    showModal.value = true;
};

const openEditModal = (item: KnowledgeItem) => {
    editingEntry.value = item;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingEntry.value = null;
};

const handleSaved = () => {
    // Reload the page to get fresh data
    window.location.reload();
};
```

**Step 2: Commit**

```bash
git add resources/js/pages/operator/KnowledgeBase.vue
git commit -m "feat: add modal state to knowledge base page"
```

---

## Task 12: Update KnowledgeBase.vue - Add UI Buttons

**Files:**
- Modify: `resources/js/pages/operator/KnowledgeBase.vue`

**Step 1: Add New Entry button to header**

Modify the header section in template:

```vue
<!-- Page Header -->
<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">
            Knowledge Base
        </h1>
        <p class="mt-2 text-gray-600">
            Manage AI frontdesk knowledge and responses
        </p>
    </div>
    <button
        @click="openCreateModal"
        class="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700"
    >
        + New Entry
    </button>
</div>
```

**Step 2: Add Edit button to each entry**

Find the entry item div and add an Edit button. Modify the entry section:

```vue
<div
    v-for="item in items"
    :key="item.id"
    class="px-6 py-4 hover:bg-gray-50"
>
    <div class="mb-2 flex items-start justify-between">
        <div class="flex-1">
            <div class="mb-1 flex items-center gap-2">
                <h3 class="text-base font-semibold text-gray-900">
                    {{ item.title }}
                </h3>
                <span
                    v-if="!item.is_active"
                    class="rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800"
                >
                    Inactive
                </span>
                <span
                    v-else
                    class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800"
                >
                    Active
                </span>
            </div>
            <div class="mb-2 text-sm text-gray-600">
                {{ item.content.substring(0, 200) }}
                <span v-if="item.content.length > 200">...</span>
            </div>
            <div
                v-if="item.keywords && item.keywords.length > 0"
                class="mb-2 flex flex-wrap gap-1"
            >
                <span
                    v-for="keyword in item.keywords"
                    :key="keyword"
                    class="rounded bg-blue-100 px-2 py-1 text-xs text-blue-800"
                >
                    {{ keyword }}
                </span>
            </div>
            <div class="text-xs text-gray-500">
                Last updated: {{ formatDate(item.updated_at) }}
                <span v-if="item.updated_by_name">
                    by {{ item.updated_by_name }}
                </span>
            </div>
        </div>
        <button
            @click="openEditModal(item)"
            class="ml-4 rounded-md bg-gray-100 px-3 py-1 text-sm text-gray-700 hover:bg-gray-200"
        >
            Edit
        </button>
    </div>
</div>
```

**Step 3: Add modal component at end of template**

Add before closing `</div>` of template:

```vue
<!-- Modal -->
<KnowledgeBaseModal
    :show="showModal"
    :entry="editingEntry"
    @close="closeModal"
    @saved="handleSaved"
/>
```

**Step 4: Commit**

```bash
git add resources/js/pages/operator/KnowledgeBase.vue
git commit -m "feat: add create/edit UI to knowledge base page"
```

---

## Task 13: Run All Tests

**Step 1: Run Pint to format code**

Run: `vendor/bin/pint --dirty`
Expected: Code formatted

**Step 2: Run all tests**

Run: `php artisan test --compact`
Expected: All tests pass

**Step 3: If tests fail, debug and fix**

If any test fails, read the error message and fix the issue, then rerun tests.

**Step 4: Commit if changes made**

```bash
git add .
git commit -m "fix: address test failures and formatting"
```

---

## Task 14: Manual Testing & Final Commit

**Step 1: Build frontend assets**

Run: `npm run build`

**Step 2: Test in browser**

1. Log in as test operator: `test@example.com` / `password`
2. Navigate to Knowledge Base
3. Click "+ New Entry" - verify modal opens
4. Fill form and save - verify entry created
5. Click "Edit" on entry - verify modal opens with data
6. Modify and save - verify entry updated
7. Click "Delete" - verify confirmation and deletion

**Step 3: Commit final changes if any**

```bash
git add .
git commit -m "feat: complete knowledge base CRUD feature"
```

**Step 4: Push to GitHub**

```bash
git push origin main
```
