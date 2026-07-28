<?php

namespace App\Http\Controllers;

use App\Models\VaptSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class VaptSystemController extends Controller
{
    // 2. Dashboard with Chart and Calendar Data
    // Dashboard with Chart, Calendar, and Table
    public function dashboard()
    {
        $statusCounts = VaptSystem::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Get all systems for calendar and table display
        $systems = VaptSystem::all();

        $calendarEvents = $systems->map(function ($system) {
            $color = match($system->status) {
                'ONGOING VAPT'     => '#60A5FA',
                'FOR PATCHING'     => '#F87171',
                'ONGOING PATCHING' => '#FB923C',
                'COMPLETED'        => '#34D399',
                default            => '#9CA3AF'
            };

            return [
                'title' => $system->name . ' (' . $system->status . ')',
                'start' => $system->updated_at->format('Y-m-d'),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff'
            ];
        });

        // Add $systems to compact()
        return view('dashboard', compact('statusCounts', 'calendarEvents', 'systems'));
    }

    // 3. CRUD: Read (Index)
    public function index()
    {
        $systems = VaptSystem::latest()->paginate(10);
        return view('vapt.index', compact('systems'));
    }

    // 3. CRUD: Create & Store
    public function store(Request $request)
    {
        // 1. Validates the input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url',
            'personnel_in_charge' => 'nullable|string|max:255',
            'status' => 'required|in:FOR PATCHING,COMPLETED,ONGOING PATCHING,ONGOING VAPT',
            'remarks' => 'nullable|string',
        ]);

        // 2. Inserts into the database table 'vapt_systems'
        VaptSystem::create($validated);

        // 3. Redirects back to the table with a success banner
        return redirect()->route('vapt.index')->with('success', 'System added to monitor.');
    }

    // 3. CRUD: Update
    public function update(Request $request, VaptSystem $vapt)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url',
            'personnel_in_charge' => 'nullable|string|max:255', // <-- Make sure this line is present
            'status' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        // Make sure $validated is passed here
        $vapt->update($validated); 

        return redirect()->route('vapt.index')->with('success', 'System updated successfully.');
    }

    // 3. CRUD: Delete (Archive via SoftDeletes)
    public function destroy(VaptSystem $vaptSystem)
    {
        $vaptSystem->delete(); // Automatically soft deletes due to model trait
        return redirect()->route('vapt.index')->with('success', 'System archived.');
    }

    // 4. Export CSV
    public function exportCsv()
    {
        // Fetch all non-archived systems
        $systems = VaptSystem::all(); 
        
        $filename = "vapt_systems_export_" . date('Y-m-d_H-i-s') . ".csv";
        
        $handle = fopen('php://output', 'w');
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return Response::stream(function() use ($handle, $systems) {
            // 1. Added 'Last Updated' to the CSV header row
            fputcsv($handle, ['ID', 'System Name', 'URL', 'Personnel In Charge', 'Status', 'Remarks', 'Date Added', 'Last Updated']);

            foreach ($systems as $system) {
                fputcsv($handle, [
                    $system->id,
                    $system->name,
                    $system->url,
                    $system->personnel_in_charge,
                    $system->status,
                    $system->remarks,
                    $system->created_at->format('M d, Y h:i A'),
                    $system->updated_at->format('M d, Y h:i A')
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}