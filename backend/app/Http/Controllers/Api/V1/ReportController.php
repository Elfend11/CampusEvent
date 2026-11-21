<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\ReportFilterRequest;
use App\Models\Event;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function dashboard(Request $request, ReportService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->dashboard($request->user()),
        ]);
    }

    public function event(
        ReportFilterRequest $request,
        Event $event,
        ReportService $service,
    ): JsonResponse {
        $this->authorize('viewReport', $event);

        return response()->json([
            'data' => $service->eventReport($event, $request->validated()),
        ]);
    }

    public function attendance(
        ReportFilterRequest $request,
        Event $event,
        ReportService $service,
    ): JsonResponse {
        $this->authorize('viewReport', $event);

        return response()->json([
            'data' => $service->attendanceReport($event, $request->validated()),
        ]);
    }
}
