<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FAQ\StoreFAQRequest;
use App\Http\Requests\FAQ\UpdateFAQRequest;
use App\Http\Resources\FAQResource;
use App\Services\Content\FAQService;

class FAQController extends Controller
{
    protected FAQService $service;

    public function __construct(FAQService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $faqs = $this->service->getAllGroupedByCategory(true);
        return response()->json(['success' => true, 'data' => $faqs]);
    }

    public function store(StoreFAQRequest $request)
    {
        $faq = $this->service->create($request->validated());
        return new FAQResource($faq);
    }

    public function update(UpdateFAQRequest $request, int $id)
    {
        $faq = $this->service->update($id, $request->validated());
        return new FAQResource($faq);
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);
        return response()->json(['message' => 'FAQ deleted']);
    }
}
