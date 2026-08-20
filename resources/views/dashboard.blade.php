<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('VAPT Status Dashboard') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- TOP BAR: Live Clock -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6 flex justify-between items-center border-l-4 border-indigo-500">
                <div class="flex items-center text-gray-700 font-medium">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm">Current Time (Manila/PH)</span>
                </div>
                <div id="liveClock" class="text-base font-bold text-gray-900 tracking-wide">
                    Loading time...
                </div>
            </div>

            <!-- MAIN SIDE-BY-SIDE GRID -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; align-items: start;">
                
                <!-- LEFT CONTAINER: Pie Chart + Status Cards (Spans 2 columns on wide screens) -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" style="grid-column: span 2 / span 2;">
                    <h3 class="text-lg font-medium text-gray-800 mb-6 border-b border-gray-100 pb-3">Status Overview</h3>
                    
                    <!-- HORIZONTAL SPLIT: Chart Left, Cards Right -->
                    <div style="display: flex; flex-direction: row; flex-wrap: wrap; gap: 2rem; align-items: center; justify-content: space-between;">
                        
                        <!-- Pie Chart (Left Side) -->
                        <div style="flex: 1 1 250px; height: 260px; position: relative; display: flex; justify-content: center; align-items: center;">
                            <canvas id="vaptChart"></canvas>
                        </div>

                        <!-- Stacked Status Cards (Right Side) -->
                        <div style="flex: 1 1 250px; display: flex; flex-direction: column; gap: 0.75rem;">
                            @php
                                $cardStyles = [
                                    'ONGOING VAPT'     => 'bg-blue-50 border-blue-100 text-blue-700',
                                    'FOR PATCHING'     => 'bg-red-50 border-red-100 text-red-700',
                                    'ONGOING PATCHING' => 'bg-orange-50 border-orange-100 text-orange-700',
                                    'COMPLETED'        => 'bg-emerald-50 border-emerald-100 text-emerald-700'
                                ];
                            @endphp

                            @foreach($cardStyles as $status => $classes)
                                <div class="flex flex-col items-center justify-center p-3 rounded-xl border transition hover:shadow-sm {{ $classes }}">
                                    <span class="text-xl font-bold mb-0.5">{{ $statusCounts[$status] ?? 0 }}</span>
                                    <span class="text-xs font-bold uppercase tracking-wider">{{ $status }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- RIGHT CONTAINER: Mini Calendar (1 column) -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5" style="grid-column: span 1 / span 1;">
                    <div class="mb-3 flex justify-between items-center">
                        <h3 class="text-base font-medium text-gray-800">Status Updates</h3>
                        <span class="text-[10px] text-gray-400 uppercase font-semibold">Calendar</span>
                    </div>
                    
                    <!-- Calendar element -->
                    <div id="calendar" class="text-xs"></div>
                </div>

            </div>
            <!-- MONITORED SYSTEMS READ-ONLY TABLE -->
                        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <div class="mb-4 flex justify-center items-center border-b border-gray-100 pb-3">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-800">Monitored Systems Overview</h3>
                                    <p class="text-xs text-gray-400 mt-0.5">Live status records of all monitored systems.</p>
                                </div>
                            </div>

                            <div class="overflow-x-auto w-full">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">System Name</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Personnel In Charge</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                        @forelse ($systems as $system)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                                    {{ $system->name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-blue-600">
                                                    @if($system->url)
                                                        <a href="{{ $system->url }}" target="_blank" class="hover:underline">{{ $system->url }}</a>
                                                    @else
                                                        <span class="text-gray-400">N/A</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                                    {{ $system->personnel_in_charge ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @php
                                                        $badgeClasses = match($system->status) {
                                                            'ONGOING VAPT'     => 'bg-blue-100 text-blue-800',
                                                            'FOR PATCHING'     => 'bg-red-100 text-red-800',
                                                            'ONGOING PATCHING' => 'bg-orange-100 text-orange-800',
                                                            'COMPLETED'        => 'bg-green-100 text-green-800',
                                                            default            => 'bg-gray-100 text-gray-800'
                                                        };
                                                    @endphp
                                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClasses }}">
                                                        {{ $system->status }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500 max-w-[250px] whitespace-normal break-words">
                                                    {{ $system->remarks }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-xs">
                                                    {{ $system->updated_at->format('M d, Y h:i A') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-4 text-center text-gray-400">
                                                    No monitored systems found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                </div>

        </div>

    <!-- Chart.js and FullCalendar Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // 1. Live Clock (Asia/Manila)
            function updateClock() {
                const clockElement = document.getElementById('liveClock');
                const now = new Date();
                
                const options = {
                    timeZone: 'Asia/Manila',
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                };
                
                clockElement.textContent = new Intl.DateTimeFormat('en-US', options).format(now);
            }
            updateClock();
            setInterval(updateClock, 1000);

            // 2. Pie Chart Initialization
            const ctx = document.getElementById('vaptChart');
            const rawData = @json($statusCounts);
            
            const labels = ['ONGOING VAPT', 'FOR PATCHING', 'ONGOING PATCHING', 'COMPLETED'];
            const dataValues = labels.map(label => rawData[label] || 0);

            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: dataValues,
                        backgroundColor: ['#60A5FA', '#F87171', '#FB923C', '#34D399'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 12,
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });

            // 3. Mini Calendar Initialization
            // 2. Initialize Mini Status Calendar
            const calendarEl = document.getElementById('calendar');
            const eventsData = @json($calendarEvents);

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'today'
                },
                events: eventsData,
                height: 360,
                contentHeight: 300,
                
                // --- COLLAPSE TO FIGURES WHEN 4 OR MORE EVENTS EXIST ---
                dayMaxEvents: 3, // Shows up to 3 individual event badges; 4 or more automatically collapses into a figure "+X more" badge

                // Custom formatting for the collapsed figure badge
                moreLinkContent: function(args) {
                    return '+' + args.num + ' systems';
                },

                // Show a popover list when clicking the figure badge
                moreLinkClick: 'popover',

                eventDisplay: 'block'
            });

            calendar.render();
        });
    </script>
    
    <!-- Custom CSS Overrides -->
        <style>
        .mini-calendar-wrapper .fc {
            font-size: 0.75rem;
        }
        .mini-calendar-wrapper .fc-toolbar-title {
            font-size: 0.95rem !important;
            font-weight: 700;
        }
        .mini-calendar-wrapper .fc-button {
            padding: 0.2rem 0.4rem !important;
            font-size: 0.7rem !important;
            border-radius: 0.5rem !important;
        }
        .mini-calendar-wrapper .fc-daygrid-day-frame {
            min-height: 42px !important;
        }
        .mini-calendar-wrapper .fc-event {
            font-size: 0.65rem !important;
            padding: 1px 3px !important;
            border-radius: 4px;
        }

        /* CUSTOM NUMERICAL FIGURE BADGE (+X MORE) STYLING */
        .mini-calendar-wrapper .fc-more-link {
            background-color: #EEF2FF !important; /* Soft indigo background */
            color: #4F46E5 !important;            /* Indigo text */
            font-weight: 700 !important;
            font-size: 0.65rem !important;
            padding: 1px 5px !important;
            border-radius: 6px !important;
            display: inline-block !important;
            margin-top: 1px !important;
        }
        .mini-calendar-wrapper .fc-more-link:hover {
            background-color: #E0E7FF !important;
            text-decoration: none !important;
        }
    </style>

    <!-- MINIMALIST FOOTER -->
    <footer class="mt-12 py-6 border-t border-gray-100 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center text-xs text-gray-500 space-y-2 sm:space-y-0">
            <div class="flex items-center space-x-2">
                <span class="font-semibold text-gray-700">Philippine Navy VAPT Status System</span>
                <span> &bull; </span>
                <span>Powered by Cyber Warfare Force (P)</span>
            </div>
            <div>
                &copy; {{ date('Y') }} All Rights Reserved.
            </div>
        </div>
    </footer>
</x-app-layout>