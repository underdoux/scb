<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LogController extends Controller
{
    /**
     * Display a listing of logs
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'type' => ['nullable', 'string', 'in:info,warning,error,success'],
            'search' => 'nullable|string|max:100',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        $query = Log::query()
            ->where('user_id', Auth::id())
            ->with(['post' => function ($query) {
                $query->select('id', 'platform', 'status');
            }]);

        // Apply type filter
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('message', 'like', "%{$filters['search']}%")
                  ->orWhereRaw("LOWER(context::text) LIKE ?", ['%' . strtolower($filters['search']) . '%']);
            });
        }

        // Apply date range filter
        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        // Get logs with pagination
        $logs = $query->latest()
            ->paginate($filters['per_page'] ?? 25)
            ->through(function ($log) {
                return [
                    'id' => $log->id,
                    'type' => $log->type,
                    'message' => $log->message,
                    'context' => $log->context,
                    'post' => $log->post ? [
                        'id' => $log->post->id,
                        'platform' => $log->post->platform,
                        'status' => $log->post->status,
                    ] : null,
                    'created_at' => $log->created_at->diffForHumans(),
                    'created_at_formatted' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            });

        // Get log statistics
        $stats = [
            'total' => Log::where('user_id', Auth::id())->count(),
            'success' => Log::where('user_id', Auth::id())->where('type', 'success')->count(),
            'error' => Log::where('user_id', Auth::id())->where('type', 'error')->count(),
            'warning' => Log::where('user_id', Auth::id())->where('type', 'warning')->count(),
            'info' => Log::where('user_id', Auth::id())->where('type', 'info')->count(),
        ];

        return Inertia::render('Logs/Index', [
            'logs' => $logs,
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    /**
     * Clear all logs for the authenticated user
     */
    public function clear()
    {
        Log::where('user_id', Auth::id())->delete();

        return redirect()->route('logs.index')
            ->with('success', 'All logs have been cleared.');
    }

    /**
     * Export logs as CSV
     */
    public function export(Request $request)
    {
        $filters = $request->validate([
            'type' => ['nullable', 'string', 'in:info,warning,error,success'],
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        $query = Log::query()
            ->where('user_id', Auth::id())
            ->with(['post']);

        // Apply filters
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        $logs = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="logs.csv"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Type', 'Message', 'Context', 'Post ID', 'Platform', 'Status']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->type,
                    $log->message,
                    json_encode($log->context),
                    $log->post?->id,
                    $log->post?->platform,
                    $log->post?->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
