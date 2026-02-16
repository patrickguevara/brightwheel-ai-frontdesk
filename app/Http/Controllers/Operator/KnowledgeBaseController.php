<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKnowledgeBaseRequest;
use App\Http\Requests\UpdateKnowledgeBaseRequest;
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
}
