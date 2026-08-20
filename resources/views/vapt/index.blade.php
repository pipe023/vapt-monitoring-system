<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Monitored Systems Management') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('vapt.export') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-sm rounded-xl transition">
                    Export CSV
                </a>

                @if(auth()->user()->isAdmin())
                    <!-- ADD SYSTEM BUTTON -->
                    <button type="button" onclick="openAddModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow-sm transition">
                        + Add System
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-100 text-green-700 rounded-xl text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <!-- SYSTEMS TABLE -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">System Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">URL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Personnel In Charge</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                                
                                @if(auth()->user()->isAdmin())
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-sm">
                            @forelse ($systems as $system)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $system->name }}</td>
                                    <td class="px-6 py-4 text-blue-600">
                                        @if($system->url)
                                            <a href="{{ $system->url }}" target="_blank" class="hover:underline">{{ $system->url }}</a>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">{{ $system->personnel_in_charge ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $badgeClasses = match($system->status) {
                                                'ONGOING VAPT'     => 'bg-blue-100 text-blue-800',
                                                'FOR PATCHING'     => 'bg-red-100 text-red-800',
                                                'ONGOING PATCHING' => 'bg-orange-100 text-orange-800',
                                                'COMPLETED'        => 'bg-green-100 text-green-800',
                                                default            => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full {{ $badgeClasses }}">
                                            {{ $system->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 max-w-xs truncate">{{ $system->remarks ?? 'N/A' }}</td>

                                    @if(auth()->user()->isAdmin())
                                        <td class="px-6 py-4 text-right space-x-2">
                                            <!-- EDIT BUTTON (Visible to Admin & Superadmin) -->
                                            <button type="button" data-system="{{ $system }}" onclick="openEditModal(this)" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                                Edit
                                            </button>
                                            
                                            <!-- DELETE BUTTON (Restricted to Superadmin ONLY) -->
                                            @if(auth()->user()->isSuperAdmin())
                                                <form action="{{ route('vapt.destroy', $system) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this system?')" class="text-red-600 hover:text-red-900 font-semibold ml-2">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->isAdmin() ? '6' : '5' }}" class="px-6 py-4 text-center text-gray-400">
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

    @if(auth()->user()->isAdmin())
        <!-- ====================================================== -->
        <!-- ADD SYSTEM MODAL -->
        <!-- ====================================================== -->
        <div id="addSystemModal" class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-md z-50 flex items-center justify-center transition-opacity duration-300 opacity-0">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-lg mx-4 overflow-hidden transform transition-all duration-300 scale-95" id="addModalContainer">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-base font-bold text-gray-800">Add Monitored System</h3>
                    <button type="button" onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold transition">&times;</button>
                </div>

                <form action="{{ route('vapt.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">System Name</label>
                        <input type="text" name="name" required placeholder="e.g. Finance Portal" class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">URL (Optional)</label>
                        <input type="url" name="url" placeholder="https://example.com" class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Personnel In Charge</label>
                        <input type="text" name="personnel_in_charge" placeholder="e.g. John Doe" class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Status</label>
                        <select name="status" required class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="ONGOING VAPT">ONGOING VAPT</option>
                            <option value="FOR PATCHING">FOR PATCHING</option>
                            <option value="ONGOING PATCHING">ONGOING PATCHING</option>
                            <option value="COMPLETED">COMPLETED</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Remarks</label>
                        <textarea name="remarks" rows="2" placeholder="Add optional notes..." class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    <div class="pt-4 flex justify-end space-x-2">
                        <button type="button" onclick="closeAddModal()" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium text-sm rounded-xl hover:bg-gray-200 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-xl hover:bg-indigo-700 shadow-sm transition">Save System</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- EDIT SYSTEM MODAL -->
        <!-- ====================================================== -->
        <div id="editSystemModal" class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-md z-50 flex items-center justify-center transition-opacity duration-300 opacity-0">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-lg mx-4 overflow-hidden transform transition-all duration-300 scale-95" id="editModalContainer">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-base font-bold text-gray-800">Edit Monitored System</h3>
                    <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold transition">&times;</button>
                </div>

                <form id="editForm" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">System Name</label>
                        <input type="text" id="edit_name" name="name" required class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">URL</label>
                        <input type="url" id="edit_url" name="url" class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Personnel In Charge</label>
                        <input type="text" id="edit_personnel" name="personnel_in_charge" class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Status</label>
                        <select id="edit_status" name="status" required class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="ONGOING VAPT">ONGOING VAPT</option>
                            <option value="FOR PATCHING">FOR PATCHING</option>
                            <option value="ONGOING PATCHING">ONGOING PATCHING</option>
                            <option value="COMPLETED">COMPLETED</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Remarks</label>
                        <textarea id="edit_remarks" name="remarks" rows="2" class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    <div class="pt-4 flex justify-end space-x-2">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium text-sm rounded-xl hover:bg-gray-200 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-xl hover:bg-indigo-700 shadow-sm transition">Update System</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- FIXED JAVASCRIPT ANIMATIONS -->
    <script>
        function openAddModal() {
            const modal = document.getElementById('addSystemModal');
            const container = document.getElementById('addModalContainer');
            
            modal.classList.remove('hidden'); // Show block so we can animate
            
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.classList.add('opacity-100');
                container.classList.remove('scale-95');
                container.classList.add('scale-100');
            }, 10);
        }

        function closeAddModal() {
            const modal = document.getElementById('addSystemModal');
            const container = document.getElementById('addModalContainer');
            
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            container.classList.remove('scale-100');
            container.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden'); // Completely hide to prevent click blocking
            }, 300);
        }

        function openEditModal(buttonElement) {
            // Safely parse JSON from the data attribute
            const system = JSON.parse(buttonElement.getAttribute('data-system'));
            
            document.getElementById('editForm').action = '/vapt/' + system.id;
            document.getElementById('edit_name').value = system.name || '';
            document.getElementById('edit_url').value = system.url || '';
            document.getElementById('edit_personnel').value = system.personnel_in_charge || '';
            document.getElementById('edit_status').value = system.status || 'ONGOING VAPT';
            document.getElementById('edit_remarks').value = system.remarks || '';
            
            const modal = document.getElementById('editSystemModal');
            const container = document.getElementById('editModalContainer');
            
            modal.classList.remove('hidden');
            
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.classList.add('opacity-100');
                container.classList.remove('scale-95');
                container.classList.add('scale-100');
            }, 10);
        }

        function closeEditModal() {
            const modal = document.getElementById('editSystemModal');
            const container = document.getElementById('editModalContainer');
            
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            container.classList.remove('scale-100');
            container.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden'); 
            }, 300);
        }
    </script>
</x-app-layout>