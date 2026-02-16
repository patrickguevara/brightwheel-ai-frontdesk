# Knowledge Base CRUD Design

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Enable operators to create, edit, and delete knowledge base entries through the operator dashboard

**Architecture:** Add KnowledgeBaseController with RESTful CRUD endpoints, modal-based editing UI in Vue, Form Request validation

**Tech Stack:** Laravel 12, Vue 3, Inertia.js v2, Tailwind CSS v4

---

## Architecture & Routes

**New Controller:** `App\Http\Controllers\Operator\KnowledgeBaseController`

**Routes:** Added to `operator` route group in `web.php`:
- `GET /operator/knowledge-base/{id}` - Get single entry (for editing)
- `POST /operator/knowledge-base` - Create new entry
- `PUT /operator/knowledge-base/{id}` - Update existing entry
- `DELETE /operator/knowledge-base/{id}` - Delete entry

**Authentication:** All routes protected by existing `auth` middleware, automatically track `updated_by` using `auth()->id()`

**Response Type:** Return JSON for API-style responses (create/update/delete)

## Backend Validation & Logic

**Form Requests:**
- `StoreKnowledgeBaseRequest` - Validation for creating entries
- `UpdateKnowledgeBaseRequest` - Validation for updating entries

**Validation Rules:**
- **Category:** Required, must be one of: hours, tuition, enrollment, health, meals, schedule, pickup, safety, classrooms, policies, general
- **Title:** Required, string, max 255 characters
- **Content:** Required, text (no max length)
- **Keywords:** Optional, array of strings
- **Is Active:** Boolean, defaults to true

**Controller Methods:**
- `store()` - Create new entry, set `updated_by` to current user
- `update()` - Update existing entry, update `updated_by`
- `destroy()` - Hard delete entry

**Success/Error Handling:**
- Success: Return 200 with success message
- Validation errors: Laravel auto-handles with 422
- Not found: 404 for edit/update/delete of non-existent entries

## Frontend UI & Modal

**New Component:** `resources/js/components/operator/KnowledgeBaseModal.vue`

**Form Fields:**
- Category: Dropdown with all 11 categories
- Title: Text input
- Content: Textarea (8-10 rows)
- Keywords: Textarea (one keyword per line, placeholder: "Enter keywords, one per line")
- Is Active: Toggle switch (green when active)

**Buttons:**
- Save (green)
- Cancel (gray)
- Delete (red, only shown when editing)

**States:**
- Create mode: "Create Knowledge Base Entry" title, no delete button
- Edit mode: "Edit Knowledge Base Entry" title, delete button visible

**Keywords Handling:**
- User enters one keyword per line in textarea
- Frontend converts to array on save: `["hours", "open", "close"]`
- Display back: Join array with newlines for editing

**KnowledgeBase.vue Updates:**
- Add "+ New Entry" button at page top
- Add "Edit" button on each entry card
- Click Edit → Modal opens with pre-filled data
- Click New → Modal opens empty
- After save/delete: Reload knowledge base data, close modal

**User Feedback:**
- Success: Green toast notification ("Entry created/updated/deleted")
- Validation errors: Red text under fields
- Loading state: Disable buttons, show spinner during save/delete

## Data Flow

### Create Flow
1. User clicks "+ New Entry" → Modal opens (create mode)
2. User fills form, clicks Save
3. Frontend validates (non-empty fields), converts keywords textarea to array
4. POST to `/operator/knowledge-base` with form data
5. Backend validates via `StoreKnowledgeBaseRequest`
6. Create entry with `updated_by` = current user ID
7. Return success response
8. Frontend shows success toast, closes modal, reloads knowledge base list

### Update Flow
1. User clicks "Edit" on entry → Modal opens (edit mode) with pre-filled data
2. User modifies fields, clicks Save
3. Frontend validates, converts keywords
4. PUT to `/operator/knowledge-base/{id}` with form data
5. Backend validates via `UpdateKnowledgeBaseRequest`
6. Update entry, set `updated_by` = current user ID, touch `updated_at`
7. Return success response
8. Frontend shows success toast, closes modal, reloads list

### Delete Flow
1. User clicks "Delete" in edit modal → Confirmation dialog ("Are you sure? This cannot be undone.")
2. User confirms
3. DELETE to `/operator/knowledge-base/{id}`
4. Backend hard deletes entry
5. Return success response
6. Frontend shows success toast, closes modal, reloads list

### Error Handling
- 422 Validation: Display field errors under inputs
- 404 Not Found: Show error toast "Entry not found"
- 500 Server Error: Show error toast "Something went wrong"

## Testing

**Feature Tests** (using Pest):

**Knowledge Base CRUD Tests:**
- `test_authenticated_user_can_create_knowledge_base_entry()`
  - POST with valid data → 200, entry created in DB
- `test_create_requires_category_title_and_content()`
  - POST with missing fields → 422 validation errors
- `test_category_must_be_valid_enum_value()`
  - POST with invalid category → 422 validation error
- `test_authenticated_user_can_update_knowledge_base_entry()`
  - PUT with valid data → 200, entry updated, updated_by tracked
- `test_authenticated_user_can_delete_knowledge_base_entry()`
  - DELETE → 200, entry removed from DB
- `test_keywords_are_stored_as_array()`
  - POST with keywords → Verify JSON array in DB
- `test_unauthenticated_user_cannot_access_crud_endpoints()`
  - POST/PUT/DELETE without auth → 401/302 redirect

**Test Coverage Goal:** All backend CRUD operations and validation rules

## Implementation Notes

- Skip seasonal and date fields (is_seasonal, effective_date, expiry_date) - not currently used
- Use Wayfinder for type-safe route generation in frontend
- Use existing Inertia patterns for form handling
- Follow existing operator UI styling (Tailwind classes, color scheme)
- Hard delete entries (no soft deletes) since wrong information should be removed completely
