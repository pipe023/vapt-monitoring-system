<?php

namespace App\Http\Controllers;

use App\Models\VaptSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            fputcsv($file, ['Network', 'System Name', 'URL', 'Personnel In Charge', 'Status', 'Remarks', 'Last Updated']);

            $networkGroups = $systems->groupBy(fn ($system) => $system->network ?: 'UNASSIGNED');
            $networkOrder = ['RED NETWORK', 'GRAY NETWORK', 'UNASSIGNED'];
            $hasWrittenGroup = false;

            foreach ($networkOrder as $network) {
                if (! $networkGroups->has($network)) {
                    continue;
                }

                if ($hasWrittenGroup) {
                    fputcsv($file, []);
                }

                foreach ($networkGroups->get($network) as $system) {
                    fputcsv($file, [
                        $network,
                        $system->name,
                        $system->url,
                        $system->personnel_in_charge,
                        $system->status,
                        $system->remarks,
                        $system->updated_at->format('Y-m-d H:i:s')
                    ]);
                }

                $hasWrittenGroup = true;
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
                'Inspection' => '#10B981',
                'Training'   => '#3B82F6',
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
                    'reference_url'     => $act->reference_path ? route('calendar.activity.reference', $act->id) : null,
                    'reference_name'    => $act->reference_name,
                    'completed_at'     => $act->completed_at?->format('Y-m-d Hi') . ($act->completed_at ? 'H' : ''),
                    'completion_reference_url' => $act->completion_reference_path ? route('calendar.activity.completion-reference', $act->id) : null,
                    'completion_reference_name' => $act->completion_reference_name,
                ]
            ];
        });

        // Calendar displays created activities only.
        $calendarEvents = $activityEvents->values()->toArray();
        $completedActivities = $activities->whereNotNull('completed_at')->sortByDesc('completed_at');

        return view('calendar', compact('calendarEvents', 'completedActivities'));
    }

    /**
     * Store a new calendar activity.
     */
    public function storeActivity(Request $request)
    {
        $request->validate([
            'type'       => 'required|in:Conference,Dispatch,Mission,TIAC,Inspection,Training',
            'start_time' => 'required|date',
            'end_time'   => 'nullable|date|after_or_equal:start_time',
            'reference_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ]);

        $referencePath = $request->file('reference_file')?->store('calendar-references', 'local');

        $usesReducedFields = in_array($request->type, ['Inspection', 'Training'], true);

        CalendarActivity::create([
            'type'              => $request->type,
            'agenda'            => $request->agenda,
            'start_time'        => Carbon::parse($request->start_time),
            'end_time'          => $request->end_time ? Carbon::parse($request->end_time) : null,
            'presiding_officer' => $usesReducedFields ? null : $request->presiding_officer,
            'attendees'         => $request->attendees,
            'venue'             => $request->venue,
            'personnel'         => $usesReducedFields ? null : $request->personnel,
            'location'          => $usesReducedFields ? null : $request->location,
            'note'              => $request->note,
            'reference_path'    => $referencePath,
            'reference_name'    => $request->file('reference_file')?->getClientOriginalName(),
            'user_id'           => auth()->id(),
        ]);

        return redirect()->route('calendar')->with('success', 'Activity added successfully.');
    }

    /**
     * Update an existing calendar activity.
     */
    public function updateActivity(Request $request, $id)
    {
        $activity = CalendarActivity::findOrFail($id);

        $request->validate([
            'type'       => 'required|in:Conference,Dispatch,Mission,TIAC,Inspection,Training',
            'start_time' => 'required|date',
            'end_time'   => 'nullable|date|after_or_equal:start_time',
            'agenda'     => 'nullable|string',
            'reference_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ]);

        $usesReducedFields = in_array($request->type, ['Inspection', 'Training'], true);

        $activity->update([
            'type'              => $request->type,
            'agenda'            => $request->agenda,
            'start_time'        => Carbon::parse($request->start_time),
            'end_time'          => $request->end_time ? Carbon::parse($request->end_time) : null,
            'presiding_officer' => $usesReducedFields ? null : $request->presiding_officer,
            'attendees'         => $request->attendees,
            'venue'             => $request->venue,
            'personnel'         => $usesReducedFields ? null : $request->personnel,
            'location'          => $usesReducedFields ? null : $request->location,
            'note'              => $request->note,
        ]);

        if ($request->hasFile('reference_file')) {
            if ($activity->reference_path) {
                Storage::disk('local')->delete($activity->reference_path);
            }

            $activity->update([
                'reference_path' => $request->file('reference_file')->store('calendar-references', 'local'),
                'reference_name' => $request->file('reference_file')->getClientOriginalName(),
            ]);
        }

        return redirect()->route('calendar')->with('success', 'Activity updated successfully.');
    }

    /**
     * Delete a calendar activity.
     */
    public function destroyActivity($id)
    {
        // Viewers may delete calendar activities, but cannot edit them.
        if (!auth()->user()->isAdmin() && !auth()->user()->isViewer()) {
            abort(403, 'Unauthorized action.');
        }

        $activity = CalendarActivity::findOrFail($id);
        if ($activity->reference_path) {
            Storage::disk('local')->delete($activity->reference_path);
        }
        if ($activity->completion_reference_path) {
            Storage::disk('local')->delete($activity->completion_reference_path);
        }
        $activity->delete();

        return redirect()->route('calendar')->with('success', 'Activity deleted successfully.');
    }

    /**
     * Download an activity reference file.
     */
    public function downloadActivityReference($id)
    {
        $activity = CalendarActivity::findOrFail($id);

        abort_unless($activity->reference_path && Storage::disk('local')->exists($activity->reference_path), 404);

        return Storage::disk('local')->download($activity->reference_path, $activity->reference_name);
    }

    /**
     * Mark an activity complete with its required completion memo.
     */
    public function completeActivity(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $activity = CalendarActivity::findOrFail($id);

        if ($activity->completed_at) {
            return redirect()->route('calendar')->with('error', 'This activity is already completed.');
        }

        $request->validate([
            'completion_reference_file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ]);

        $file = $request->file('completion_reference_file');
        $activity->update([
            'completed_at' => now(),
            'completed_by' => auth()->id(),
            'completion_reference_path' => $file->store('calendar-completion-references', 'local'),
            'completion_reference_name' => $file->getClientOriginalName(),
        ]);

        return redirect()->route('calendar')->with('success', 'Activity completed successfully.');
    }

    /**
     * Download a completion memo.
     */
    public function downloadCompletionReference($id)
    {
        $activity = CalendarActivity::findOrFail($id);

        abort_unless($activity->completion_reference_path && Storage::disk('local')->exists($activity->completion_reference_path), 404);

        return Storage::disk('local')->download($activity->completion_reference_path, $activity->completion_reference_name);
    }
}