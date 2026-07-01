<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;
use App\Models\Setting;

class ActivityLogController extends Controller
{
    public function index()
    {
        $this->authorize('viewAuditLog');

        $perPage = Setting::get('rows_per_page', 20);

        $logs = Activity::with('causer')
            ->latest()
            ->paginate($perPage)
            ->through(fn($log) => [
                'id'           => $log->id,
                'event'        => $log->event,
                'description'  => $log->description,
                'subject_type' => class_basename($log->subject_type),
                'subject_id'   => $log->subject_id,
                'causer_name'  => $log->causer?->name ?? 'System',
                'properties'   => $log->properties,
                'created_at'   => $log->created_at->format('Y-m-d H:i:s'),
                'changes'      => $log->properties?->get('attributes') ?? [],
                'old'          => $log->properties?->get('old') ?? [],
            ]);

        return Inertia::render('Admin/AuditLog', [
            'logs' => $logs,
        ]);
    }
}