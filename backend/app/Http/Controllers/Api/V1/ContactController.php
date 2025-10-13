<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\ContactRequest;
use App\Http\Resources\ContactSubmissionResource;
use App\Services\Contact\ContactService;

class ContactController extends Controller
{
    protected ContactService $service;

    public function __construct(ContactService $service)
    {
        $this->service = $service;
    }

    public function store(ContactRequest $request)
    {
        $submission = $this->service->submit($request->validated());
        return new ContactSubmissionResource($submission);
    }
}
