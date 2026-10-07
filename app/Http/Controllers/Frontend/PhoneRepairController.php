<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\FrontendPageService;
use App\Services\FrontendRepairService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PhoneRepairController extends Controller
{
    public function index(Request $request, FrontendPageService $pages, FrontendRepairService $repairs): View
    {
        $data = $pages->forSlug('phone-repair', $request);
        $services = $repairs->services();
        $data['repair'] = [
            'brands' => $repairs->brands(),
            'services' => $services,
            'featuredServices' => $services->where('is_featured', true)->values(),
        ];

        return view('frontend.phone-repair', $data);
    }

    public function models(Request $request, FrontendRepairService $repairs): JsonResponse
    {
        $validated = $request->validate([
            'brand_id' => ['required', 'integer'],
        ]);

        return response()->json([
            'data' => $repairs->modelsForBrand((int) $validated['brand_id']),
        ]);
    }

    public function estimate(Request $request, FrontendRepairService $repairs): JsonResponse
    {
        $validated = $request->validate([
            'device_model_id' => ['required', 'integer'],
            'repair_service_id' => ['required', 'integer'],
        ]);

        return response()->json($repairs->estimate(
            (int) $validated['device_model_id'],
            (int) $validated['repair_service_id']
        ));
    }
}
