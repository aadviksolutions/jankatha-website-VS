<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NewsFetchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewsCronController extends Controller
{
    /**
     * Handle incoming secure cron trigger from Vercel or external scheduler.
     */
    public function fetch(Request $request, NewsFetchService $service): JsonResponse
    {
        $configuredSecret = env('CRON_SECRET');

        // Extract token from Bearer authorization header, custom header, or query param
        $bearerToken = $request->bearerToken();
        $headerSecret = $request->header('X-Cron-Secret');
        $querySecret = $request->query('key');

        $providedSecret = $bearerToken ?: ($headerSecret ?: $querySecret);

        if (! empty($configuredSecret)) {
            if (empty($providedSecret) || ! hash_equals((string) $configuredSecret, (string) $providedSecret)) {
                Log::warning('Unauthorized attempt to trigger news fetch cron endpoint from IP: '.$request->ip());

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Invalid or missing cron secret.',
                ], 401);
            }
        } elseif (! app()->environment('local', 'testing')) {
            // In production, CRON_SECRET must be set
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: CRON_SECRET is not configured on this server.',
            ], 403);
        }

        @set_time_limit(50);

        try {
            $force = $request->boolean('force');
            $sourceId = $request->filled('source') ? (int) $request->input('source') : null;

            $stats = $service->fetchActiveSources($sourceId, $force);

            return response()->json([
                'success' => true,
                'timestamp' => now()->toIso8601String(),
                'sources_processed' => $stats['sources_processed'],
                'items_found' => $stats['items_found'],
                'items_imported' => $stats['items_imported'],
                'items_skipped_duplicate' => $stats['items_skipped_duplicate'],
                'errors' => $stats['errors'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Cron news fetch execution error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Execution error during news fetch.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
