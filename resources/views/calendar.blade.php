<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Calendar of Activities') }}
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">Real-time schedule tracking for activities and security assessments.</p>
            </div>

            <!-- ADD ACTIVITY BUTTON -->
            <button type="button" onclick="openActivityModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs rounded-xl shadow-sm transition">
                + Add Activity
            </button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <!-- Flash Success Message -->
            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-100 text-green-700 rounded-xl text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <!-- LEGEND -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-center gap-3 text-xs font-semibold">
                <span class="text-gray-400 uppercase tracking-wider text-[10px]">Legend:</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-purple-100 text-purple-800"><span class="w-2 h-2 mr-1.5 bg-purple-500 rounded-full"></span> Conference</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-cyan-100 text-cyan-800"><span class="w-2 h-2 mr-1.5 bg-cyan-500 rounded-full"></span> Dispatch</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-pink-100 text-pink-800"><span class="w-2 h-2 mr-1.5 bg-pink-500 rounded-full"></span> Mission</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-100 text-amber-800"><span class="w-2 h-2 mr-1.5 bg-amber-500 rounded-full"></span> TIAC</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-red-100 text-red-800"><span class="w-2 h-2 mr-1.5 bg-red-500 rounded-full"></span> VAPT Status</span>
            </div>

            <!-- MAIN CALENDAR CONTAINER -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 calendar-card-container">
        <div id="monitoringCalendar" class="w-full"></div>
    </div>

        </div>
    </div>

    <!-- ====================================================== -->
    <!-- ADD ACTIVITY MODAL (WITH BLUR BACKDROP)                -->
    <!-- ====================================================== -->
    <div id="addActivityModal" class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-md z-50 flex items-center justify-center transition-opacity duration-300 opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-lg mx-4 overflow-hidden transform transition-all duration-300 scale-95" id="addActivityModalContainer">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-800">Add Calendar Activity</h3>
                <button type="button" onclick="closeActivityModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold transition">&times;</button>
            </div>

            <form action="{{ route('calendar.activity.store') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <!-- ACTIVITY TYPE SELECTION -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Activity Type</label>
                    <select id="activity_type" name="type" required onchange="handleTypeChange()" class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="Conference">Conference</option>
                        <option value="Dispatch">Dispatch</option>
                        <option value="Mission">Mission</option>
                        <option value="TIAC">TIAC</option>
                    </select>
                </div>

                <!-- DATE & TIME -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Start Date & Time</label>
                        <input type="datetime-local" name="start_time" required class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">End Date & Time (Optional)</label>
                        <input type="datetime-local" name="end_time" class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- DYNAMIC FIELDS FOR CONFERENCE / TIAC -->
                <div id="fields_conference_tiac" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Agenda</label>
                        <input type="text" name="agenda" placeholder="e.g. Annual Planning Session" class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Presiding Officer</label>
                        <input type="text" name="presiding_officer" placeholder="e.g. John Doe" class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Attendees</label>
                        <textarea name="attendees" rows="2" placeholder="List of attendees..." class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Venue</label>
                        <input type="text" name="venue" placeholder="e.g. Conference Room A" class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- DYNAMIC FIELDS FOR DISPATCH / MISSION -->
                <div id="fields_dispatch_mission" class="space-y-4 hidden">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Personnel</label>
                        <textarea name="personnel" rows="2" placeholder="Assigned personnel..." class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Location</label>
                        <input type="text" name="location" placeholder="e.g. Regional Office" class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- COMMON NOTE FIELD -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Note</label>
                    <textarea name="note" rows="2" placeholder="Additional details..." class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <div class="pt-4 flex justify-end space-x-2 border-t border-gray-100">
                    <button type="button" onclick="closeActivityModal()" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium text-sm rounded-xl hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-xl hover:bg-indigo-700 shadow-sm transition">Save Activity</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ====================================================== -->
    <!-- DETAILS POPUP MODAL                                    -->
    <!-- ====================================================== -->
    <div id="detailsModal" class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-md z-50 flex items-center justify-center transition-opacity duration-300 opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md mx-4 overflow-hidden transform transition-all duration-300 scale-95" id="detailsModalContainer">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-base font-bold text-gray-800" id="modal_title">Event Details</h3>
                <button type="button" onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold transition">&times;</button>
            </div>

            <div class="p-6 space-y-3 text-sm text-gray-700" id="modal_body">
                <!-- Dynamically populated via JS -->
            </div>

            <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button type="button" onclick="closeDetailsModal()" class="px-4 py-2 bg-gray-200 text-gray-700 font-medium text-xs rounded-xl hover:bg-gray-300 transition">Close</button>
            </div>
        </div>
    </div>

    <!-- STYLING OVERRIDES -->
    <style>
        /* 1. Expand parent card wrapper */
        .max-w-7xl {
            max-width: 98% !important; /* Covers nearly full screen width */
        }

        /* 2. Container Card height optimization */
        .calendar-card-container {
            min-height: 82vh !important; /* Forces container to occupy 82% of screen height */
            display: flex !important;
            flex-direction: column !important;
        }

        /* 3. Force FullCalendar element to fill container completely */
        #monitoringCalendar {
            flex: 1 1 auto !important;
            height: 100% !important;
            width: 100% !important;
        }

        /* 4. Allow day cells to stretch vertically */
        .fc-daygrid-body,
        .fc-scrollgrid-sync-table {
            height: 100% !important;
            width: 100% !important;
        }

        .fc-daygrid-day-frame { 
            min-height: 110px !important; 
            height: 100% !important;
        }

        /* 5. Typography and button sizing */
        .fc .fc-toolbar-title { font-size: 1.35rem !important; font-weight: 700; color: #1F2937; }
        .fc .fc-button-primary { background-color: #4F46E5 !important; border-color: #4F46E5 !important; border-radius: 0.75rem !important; font-size: 0.85rem; font-weight: 600; padding: 0.5rem 1rem !important; }
        .fc .fc-button-primary:hover { background-color: #4338CA !important; }
        
        .fc-event { 
            cursor: pointer; 
            border-radius: 6px; 
            padding: 3px 6px; 
            margin-bottom: 3px !important;
            overflow: hidden !important; 
            white-space: nowrap !important;
        }

        /* Marquee & Static Text Handlers */
        .marquee-wrapper { display: flex !important; overflow: hidden !important; width: 100% !important; white-space: nowrap !important; }
        .static-content { display: inline-block !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; width: 100% !important; }
        .marquee-content { display: inline-block !important; white-space: nowrap !important; padding-left: 100%; animation: calendarMarquee 10s linear infinite; }
        .fc-event:hover .marquee-content { animation-play-state: paused; }

        @keyframes calendarMarquee {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-100%); }
        }
    </style>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

    <script>
        const allEvents = {!! json_encode($calendarEvents) !!};

        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('monitoringCalendar');

                calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                events: allEvents, // Pass clean array directly
                height: 720,
                aspectRatio: 1.8,
                dayMaxEvents: 3,
                displayEventTime: false,
                
                // --- DISPLAY MILITARY TIME FORMAT (e.g., 0800H / 1430H) ---
                displayEventTime: true,
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false, // 24-hour / Military format
                    meridiem: false
                },

                // Custom event marquee content
                eventContent: function(arg) {
                    let militaryTime = (arg.event.extendedProps && arg.event.extendedProps.military_start)
                        ? arg.event.extendedProps.military_start.split(' ')[1] + ' ' 
                        : '';
                    let fullTitle = militaryTime + arg.event.title;

                    // Check if the title is long enough to require scrolling (adjust threshold as needed)
                    let isLongText = fullTitle.length > 18;

                    if (isLongText) {
                        return {
                            html: `
                                <div class="marquee-wrapper">
                                    <div class="marquee-content font-medium text-xs text-white">
                                        ${fullTitle}
                                    </div>
                                </div>
                            `
                        };
                    } else {
                        return {
                            html: `
                                <div class="marquee-wrapper">
                                    <div class="static-content font-medium text-xs text-white">
                                        ${fullTitle}
                                    </div>
                                </div>
                            `
                        };
                    }
                },

                // Append 'H' to event time text
                eventDidMount: function(info) {
                    const timeEl = info.el.querySelector('.fc-event-time');
                    if (timeEl && timeEl.innerText) {
                        // Converts "08:00" to "0800H"
                        timeEl.innerText = timeEl.innerText.replace(':', '') + 'H';
                    }
                },

                moreLinkContent: function(args) { return '+' + args.num + ' events'; },
                moreLinkClick: 'popover',
                eventDisplay: 'block',

                // CLICK EVENT TO VIEW DETAILS
                eventClick: function(info) {
                const event = info.event;
                const props = event.extendedProps;
                const body = document.getElementById('modal_body');

                document.getElementById('modal_title').innerText = event.title;

                if (props.category === 'VAPT') {
                    body.innerHTML = `
                        <div>
                            <strong class="text-xs text-gray-400 uppercase block">Date / Time</strong>
                            <span class="font-semibold text-gray-800">${props.updated_at}</span>
                        </div>
                        <div>
                            <strong class="text-xs text-gray-400 uppercase block">Status</strong>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">${props.status}</span>
                        </div>
                        <div>
                            <strong class="text-xs text-gray-400 uppercase block">Personnel in Charge</strong>
                            <span>${props.personnel_in_charge}</span>
                        </div>
                        <div>
                            <strong class="text-xs text-gray-400 uppercase block">URL</strong>
                            ${props.url ? `<a href="${props.url}" target="_blank" class="text-blue-600 hover:underline">${props.url}</a>` : 'N/A'}
                        </div>
                        <div>
                            <strong class="text-xs text-gray-400 uppercase block">Remarks</strong>
                            <p class="text-xs text-gray-600 bg-gray-50 p-2 rounded-lg border border-gray-100 mt-0.5">${props.remarks}</p>
                        </div>
                    `;
                } else {
                    let dynamicInfo = '';
                    if (props.type === 'Conference' || props.type === 'TIAC') {
                        dynamicInfo = `
                            <div><strong class="text-xs text-gray-400 uppercase block">Agenda</strong> ${props.agenda}</div>
                            <div><strong class="text-xs text-gray-400 uppercase block">Presiding Officer</strong> ${props.presiding_officer}</div>
                            <div><strong class="text-xs text-gray-400 uppercase block">Attendees</strong> ${props.attendees}</div>
                            <div><strong class="text-xs text-gray-400 uppercase block">Venue</strong> ${props.venue}</div>
                        `;
                    } else {
                        dynamicInfo = `
                            <div><strong class="text-xs text-gray-400 uppercase block">Personnel</strong> ${props.personnel}</div>
                            <div><strong class="text-xs text-gray-400 uppercase block">Location</strong> ${props.location}</div>
                        `;
                    }

                    body.innerHTML = `
                        <div>
                            <strong class="text-xs text-gray-400 uppercase block">Activity Type</strong>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">${props.type}</span>
                        </div>
                        <div>
                            <strong class="text-xs text-gray-400 uppercase block">Schedule (Date & Time)</strong>
                            <span class="font-semibold text-gray-800">${props.military_start} ${props.military_end !== 'N/A' ? ' - ' + props.military_end : ''}</span>
                        </div>
                        ${dynamicInfo}
                        <div>
                            <strong class="text-xs text-gray-400 uppercase block">Note</strong>
                            <p class="text-xs text-gray-600 bg-gray-50 p-2 rounded-lg border border-gray-100 mt-0.5">${props.note}</p>
                        </div>
                    `;
                }

                openDetailsModal();
            }
            });

            calendar.render();
        });

        // HANDLE FORM DYNAMIC FIELD VISIBILITY
        function handleTypeChange() {
            const type = document.getElementById('activity_type').value;
            const confTiacGroup = document.getElementById('fields_conference_tiac');
            const dispatchMissionGroup = document.getElementById('fields_dispatch_mission');

            if (type === 'Conference' || type === 'TIAC') {
                confTiacGroup.classList.remove('hidden');
                dispatchMissionGroup.classList.add('hidden');
            } else {
                confTiacGroup.classList.add('hidden');
                dispatchMissionGroup.classList.remove('hidden');
            }
        }

        // ADD ACTIVITY MODAL TOGGLES
        function openActivityModal() {
            handleTypeChange();
            const modal = document.getElementById('addActivityModal');
            const container = document.getElementById('addActivityModalContainer');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.classList.add('opacity-100');
                container.classList.remove('scale-95');
                container.classList.add('scale-100');
            }, 10);
        }

        function closeActivityModal() {
            const modal = document.getElementById('addActivityModal');
            const container = document.getElementById('addActivityModalContainer');
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            container.classList.remove('scale-100');
            container.classList.add('scale-95');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        // DETAILS MODAL TOGGLES
        function openDetailsModal() {
            const modal = document.getElementById('detailsModal');
            const container = document.getElementById('detailsModalContainer');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.classList.add('opacity-100');
                container.classList.remove('scale-95');
                container.classList.add('scale-100');
            }, 10);
        }

        function closeDetailsModal() {
            const modal = document.getElementById('detailsModal');
            const container = document.getElementById('detailsModalContainer');
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            container.classList.remove('scale-100');
            container.classList.add('scale-95');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }
        
    </script>
</x-app-layout>