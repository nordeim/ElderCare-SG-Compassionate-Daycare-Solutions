<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CenterResource;
use App\Models\Center;

class CenterController extends Controller
{
    public function index()
    {
        $centers = Center::published()->paginate(15);
        return CenterResource::collection($centers);
    }

    public function show($slug)
    {
        $center = Center::where('slug', $slug)->firstOrFail();
        return new CenterResource($center);
    }
}
