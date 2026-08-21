<?php

namespace App\Http\Controllers;

use App\Models\VaptSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\CalendarActivity;
use Carbon\Carbon;


class VaptSystemController extends Controller
{
    /**
     * Main Dashboard (Shared by Admin & Superadmin)
     */
    public function dashboard()
    {
        $systems = VaptSystem::latest()->get();
        $networkSystems = collect([
            'RED NETWORK' => $systems->where('network', 'RED NETWORK'),
            'GRAY NETWORK' => $systems->where('network', 'GRAY NETWORK'),
            'UNASSIGNED' => $systems->whereNull('network'),
        ]);

        $statusCounts = VaptSystem::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        $networkCounts = $systems->groupBy('network')->map->count()->toArray();

        $calendarEvents = $systems->map(function ($system) {
            $color = match($system->status) {
                'ONGOING VAPT'     => '#3B82F6',
                'FOR PATCHING'     => '#EF4444',
                'ONGOING PATCHING' => '#F97316',
                'COMPLETED'        => '#10B981',
                default            => '#6B7280'
            };

            return [
                'title'           => $system->name . ' (' . $system->status . ')',
                'start'           => $system->updated_at->format('Y-m-d'),
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
            ];
        });

        return view('dashboard', compact('statusCounts', 'calendarEvents', 'systems', 'networkSystems', 'networkCounts'));
    }

    /**
     * Display a listing of the monitored systems.
     */
    public function index(Request $request)
    {
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
        $networkSystems = collect([
            'RED NETWORK' => $systems->where('network', 'RED NETWORK'),
            'GRAY NETWORK' => $systems->where('network', 'GRAY NETWORK'),
            'UNASSIGNED' => $systems->whereNull('network'),
        ]);

        return view('vapt.index', compact('systems', 'networkSystems'));
    }

    /**
     * Store a newly created system in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'network' => 'required|in:RED NETWORK,GRAY NETWORK',
            'url' => 'nullable|url|max:255',
            'personnel_in_charge' => 'nullable|string|max:255',
            'status' => 'required|string',
            'date_of_last_va' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        VaptSystem::create($request->all());

        return redirect()->route('vapt.index')->with('success', 'System added successfully.');
    }

    /**
     * Update the specified system in storage.
     */
    public function update(Request $request, string $vapt)
    {
        $vapt = VaptSystem::findOrFail(VaptSystem::decryptId($vapt));

        $request->validate([
            'name' => 'required|string|max:255',
            'network' => 'required|in:RED NETWORK,GRAY NETWORK',
            'url' => 'nullable|url|max:255',
            'personnel_in_charge' => 'nullable|string|max:255',
            'status' => 'required|string',
            'date_of_last_va' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        $vapt->update($request->all());

        return redirect()->route('vapt.index')->with('success', 'System updated successfully.');
    }

    /**
     * Remove the specified system from storage.
     */
    public function destroy(string $vapt)
    {
        $vapt = VaptSystem::findOrFail(VaptSystem::decryptId($vapt));

        // STRICT CHECK: Block Admins from deleting
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->route('vapt.index')->with('error', 'Unauthorized Action: Only Superadmins can delete systems.');
        }

        $vapt->delete();

        return redirect()->route('vapt.index')->with('success', 'System deleted successfully.');
    }

    /**
     * Export the systems to a CSV file.
     */
    public function exportCsv()
    {
        $systems = VaptSystem::latest()->get();
        $csvFileName = 'vapt_systems_report_' . date('Y_m_d_His') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($systems) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['System Name', 'URL', 'Personnel In Charge', 'Status', 'Remarks', 'Last Updated']);

            foreach ($systems as $system) {
                fputcsv($file, [
                    $system->name,
                    $system->url,
                    $system->personnel_in_charge,
                    $system->status,
                    $system->remarks,
                    $system->updated_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    /**
     * Display dedicated Calendar Monitoring view.
     */
    public function calendar()
    {
        $systems = VaptSystem::all();
        $activities = CalendarActivity::all();

        // 1. VAPT Systems Events
        $vaptEvents = $systems->map(function ($system) {
            $color = match($system->status) {
                'ONGOING VAPT'     => '#3B82F6',
                'FOR PATCHING'     => '#EF4444',
                'ONGOING PATCHING' => '#F97316',
                'COMPLETED'        => '#10B981',
                default            => '#6B7280'
            };

            return [
                'id'              => 'vapt_' . $system->id,
                'title'           => $system->name,
                'start'           => Carbon::parse($system->updated_at)->format('Y-m-d'),
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'category'            => 'VAPT',
                    'status'              => $system->status,
                    'personnel_in_charge' => $system->personnel_in_charge ?? 'N/A',
                    'url'                 => $system->url ?? '',
                    'remarks'             => $system->remarks ?? 'None',
                    'updated_at'          => Carbon::parse($system->updated_at)->format('Y-m-d Hi') . 'H',
                ]
            ];
        });

        // 2. Dynamic Activity Events
        $activityEvents = $activities->map(function ($act) {
            $color = match($act->type) {
                'Conference' => '#8B5CF6',
                'Dispatch'   => '#06B6D4',
                'Mission'    => '#EC4899',
                'TIAC'       => '#F59E0B',
                default      => '#6B7280'
            };

            $title = $act->agenda ?? $act->location ?? $act->type;

            // Clean dates for FullCalendar placement
            $startDate = Carbon::parse($act->start_time)->format('Y-m-d');
            
            // Military formatted strings for popups
            $militaryStart = Carbon::parse($act->start_time)->format('Y-m-d Hi') . 'H';
            $militaryEnd   = $act->end_time ? Carbon::parse($act->end_time)->format('Y-m-d Hi') . 'H' : 'N/A';

            return [
                'id'              => 'act_' . $act->id,
                'title'           => $title,
                'start'           => $startDate,
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'category'          => 'ACTIVITY',
                    'type'              => $act->type,
                    'activity_id'       => $act->id,
                    'start_time'        => Carbon::parse($act->start_time)->format('Y-m-d\TH:i'),
                    'end_time'          => $act->end_time ? Carbon::parse($act->end_time)->format('Y-m-d\TH:i') : '',
                    'military_start'    => $militaryStart,
                    'military_end'      => $militaryEnd,
                    'agenda'            => $act->agenda ?? 'N/A',
                    'presiding_officer' => $act->presiding_officer ?? 'N/A',
                    'attendees'         => $act->attendees ?? 'N/A',
                    'venue'             => $act->venue ?? 'N/A',
                    'personnel'         => $act->personnel ?? 'N/A',
                    'location'          => $act->location ?? 'N/A',
                    'note'              => $act->note ?? 'None',
                ]
            ];
        });

        // Calendar displays created activities only.
        $calendarEvents = $activityEvents->values()->toArray();

        return view('calendar', compact('calendarEvents'));
    }

    /**
     * Store a new calendar activity.
     */
    public function storeActivity(Request $request)
    {
        $request->validate([
            'type'       => 'required|in:Conference,Dispatch,Mission,TIAC',
            'start_time' => 'required|date',
            'end_time'   => 'nullable|date|after_or_equal:start_time',
        ]);

        CalendarActivity::create([
            'type'              => $request->type,
            'agenda'            => $request->agenda,
            'start_time'        => Carbon::parse($request->start_time),
            'end_time'          => $request->end_time ? Carbon::parse($request->end_time) : null,
            'presiding_officer' => $request->presiding_officer,
            'attendees'         => $request->attendees,
            'venue'             => $request->venue,
            'personnel'         => $request->personnel,
            'location'          => $request->location,
            'note'              => $request->note,
            'user_id'           => auth()->id(),
        ]);

        return redirect()->route('calendar')->with('success', 'Activity added successfully.');
    }

    /**
     * Update an existing calendar activity.
     */
    public function updateActivity(Request $request, $id)
    {
        // Prevent Viewers from editing
        if (auth()->user()->isViewer()) {
            abort(403, 'Unauthorized action.');
        }

        $activity = CalendarActivity::findOrFail($id);

        $request->validate([
            'type'       => 'required|string',
            'start_time' => 'required|date',
            'end_time'   => 'nullable|date|after_or_equal:start_time',
            'agenda'     => 'nullable|string',
        ]);

        $activity->update([
            'type'              => $request->type,
            'agenda'            => $request->agenda,
            'start_time'        => Carbon::parse($request->start_time),
            'end_time'          => $request->end_time ? Carbon::parse($request->end_time) : null,
            'presiding_officer' => $request->presiding_officer,
            'attendees'         => $request->attendees,
            'venue'             => $request->venue,
            'personnel'         => $request->personnel,
            'location'          => $request->location,
            'note'              => $request->note,
        ]);

        return redirect()->route('calendar')->with('success', 'Activity updated successfully.');
    }

    /**
     * Delete a calendar activity.
     */
    public function destroyActivity($id)
    {
        // Only admins and superadmins can delete
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $activity = CalendarActivity::findOrFail($id);
        $activity->delete();

        return redirect()->route('calendar')->with('success', 'Activity deleted successfully.');
    }
}