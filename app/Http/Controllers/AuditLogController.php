<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $module = $request->input('module');
        $userId = $request->input('user_id');
        $search = $request->input('search');

        $users = User::orderBy('name')->get();
        $modules = AuditLog::select('module')->distinct()->pluck('module');

        $logs = AuditLog::with('user')
            ->when($module, fn ($q) => $q->where('module', $module))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($search, function ($q, $search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('audit_logs.index', compact('logs', 'users', 'modules', 'module', 'userId', 'search'));
    }
}
