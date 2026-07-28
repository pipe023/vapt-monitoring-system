<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Monitored Systems') }}
            </h2>
            <div x-data>
                <button @click="$dispatch('open-add-modal')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition font-medium text-sm">
                    + Add New
                </button>
                <a href="{{ route('vapt.export') }}" class="ml-2 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition font-medium text-sm inline-block">
                    Export CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ editModalOpen: false, editData: {} }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Data Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto text-gray-900">
                    <table class="w-full text-left border-collapse min-w-max">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-sm uppercase tracking-wider text-gray-500">
                                <th class="px-6 py-4 font-medium">System Name</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Personnel In Charge</th>
                                <th class="px-6 py-4 font-medium">Remarks</th>
                                <th class="px-6 py-4 font-medium">Last Updated</th>
                                <th class="px-6 py-4 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($systems as $system)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ $system->name }}
                                        @if($system->url)
                                            <div class="text-xs font-normal text-blue-500 hover:underline">
                                                <a href="{{ $system->url }}" target="_blank">{{ $system->url }}</a>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $colors = [
                                                'FOR PATCHING' => 'bg-red-100 text-red-800',
                                                'ONGOING PATCHING' => 'bg-orange-100 text-orange-800',
                                                'ONGOING VAPT' => 'bg-blue-100 text-blue-800',
                                                'COMPLETED' => 'bg-green-100 text-green-800',
                                            ];
                                            $badgeClass = $colors[$system->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $badgeClass }}">
                                            {{ $system->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $system->personnel_in_charge ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ Str::limit($system->remarks, 40) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <!-- Displays exactly when it was added or last edited -->
                                        {{ $system->updated_at->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-3">
                                        <button @click="editData = {{ $system }}; editModalOpen = true" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            Edit
                                        </button>
                                        <form action="{{ route('vapt.destroy', $system) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete/archive this system?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        No systems added yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        {{ $systems->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================== -->
        <!-- Add Modal (Alpine.js)      -->
        <!-- ========================== -->
        <div x-data="{ addModalOpen: false }" @open-add-modal.window="addModalOpen = true" x-cloak>
            <div x-show="addModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="addModalOpen" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="addModalOpen = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <div x-show="addModalOpen" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Add System to Monitor</h3>
                        
                        <form action="{{ route('vapt.store') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">System Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">URL</label>
                                    <input type="url" name="url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">VAPT Status <span class="text-red-500">*</span></label>
                                    <select name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="ONGOING VAPT">ONGOING VAPT</option>
                                        <option value="FOR PATCHING">FOR PATCHING</option>
                                        <option value="ONGOING PATCHING">ONGOING PATCHING</option>
                                        <option value="COMPLETED">COMPLETED</option>
                                    </select>
                                </div>
                                <div class="mt-4">
                                    <label for="personnel_in_charge" class="block text-sm font-medium text-gray-700">Personnel In Charge</label>
                                    <input type="text" name="personnel_in_charge" id="personnel_in_charge" placeholder="e.g. John Doe" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Remarks</label>
                                    <textarea name="remarks" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end space-x-3">
                                <button type="button" @click="addModalOpen = false" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">Cancel</button>
                                <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================== -->
        <!-- Edit Modal (Alpine.js)     -->
        <!-- ========================== -->
        <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editModalOpen" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="editModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="editModalOpen" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Edit System</h3>
                    
                    <form :action="`/vapt/${editData.id}`" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">System Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="editData.name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">URL</label>
                                <input type="url" name="url" x-model="editData.url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">VAPT Status <span class="text-red-500">*</span></label>
                                <select name="status" x-model="editData.status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="ONGOING VAPT">ONGOING VAPT</option>
                                    <option value="FOR PATCHING">FOR PATCHING</option>
                                    <option value="ONGOING PATCHING">ONGOING PATCHING</option>
                                    <option value="COMPLETED">COMPLETED</option>
                                </select>
                            </div>
                            <div class="mt-4">
                                <label for="edit_personnel_in_charge_{{ $system->id }}" class="block text-sm font-medium text-gray-700">Personnel In Charge</label>
                                <input type="text" name="personnel_in_charge" id="edit_personnel_in_charge_{{ $system->id }}" value="{{ old('personnel_in_charge', $system->personnel_in_charge) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Remarks</label>
                                <textarea name="remarks" x-model="editData.remarks" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="editModalOpen = false" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">Cancel</button>
                            <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>