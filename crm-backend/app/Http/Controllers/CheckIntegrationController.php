<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Integration\Contracts\IntegrationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckIntegrationController extends Controller
{
    public function __construct(
        private IntegrationServiceInterface $integrationService
    ) {}

    public function check(Request $request): JsonResponse
    {
        $pendingIntegration = session('pending_integration');

        if (! $pendingIntegration) {
            return response()->json([
                'status' => 'no_pending',
                'message' => 'No pending integration found',
            ]);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401);
        }

        $result = $this->integrationService->checkIntegrationStatus($user);

        if ($result['status'] === 'success') {
            session()->forget('pending_integration');
        }

        $statusCode = $result['status'] === 'error' ? 500 : 200;

        return response()->json($result, $statusCode);
    }
}
