<?php

namespace App\Http\Controllers;

use App\Models\VaptSystem;
use Illuminate\Http\Request;

class ViewerController extends Controller
{
    /**
     * Display the read-only Viewer Dashboard.
     */
    public function index(Request $request)
    {
        // 1. Fetch status counts for Status Overview (Chart & Cards)
        $statusCounts = VaptSystem::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // 2. Query systems for Monitored Systems Overview (Directory Table)
        $query = VaptSystem::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('personnel_in_charge', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $systems = $query->latest()->get();
        $networkCounts = $systems->groupBy('network')->map->count()->toArray();

        // 3. Map systems to FullCalendar events for Status Calendar
        $calendarEvents = $systems->map(function ($system) {
            $color = match($system->status) {
                'ONGOING VAPT'     => '#3B82F6', // Blue
                'FOR PATCHING'     => '#EF4444', // Red
                'ONGOING PATCHING' => '#F97316', // Orange
                'COMPLETED'        => '#10B981', // Green
                default            => '#6B7280'  // Gray
            };

            return [
                'title'           => $system->name . ' (' . $system->status . ')',
                'start'           => $system->updated_at->format('Y-m-d'),
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
            ];
        });

        return view('viewer.dashboard', compact('statusCounts', 'systems', 'calendarEvents', 'networkCounts'));
    }
}