<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Superadmin User Management & Audit Logs') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Flash Alerts -->
            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-100 text-green-700 rounded-xl text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-100 text-red-700 rounded-xl text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <!-- 1. REGISTER NEW USER FORM -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100">
                    Register New Account
                </h3>

                <form method="POST" action="{{ route('register') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Username</label>
                        <input type="text" name="username" value="{{ old('username') }}" required placeholder="Enter username" class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                        <x-input-error :messages="$errors->get('username')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Role</label>
                        <select name="role" required class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="viewer">Viewer (Read-Only)</option>
                            <option value="admin">Admin (System Manager)</option>
                            <option value="superadmin">Superadmin (Full Access)</option>
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Password</label>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Confirm Password</label>
                        <div class="flex space-x-2">
                            <input type="password" name="password_confirmation" required placeholder="••••••••" class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs uppercase tracking-wider rounded-xl shadow-sm transition whitespace-nowrap">
                                Create
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- 2. USER DIRECTORY WITH RESET PASSWORD ACTIONS -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100">
                    Existing Accounts Management
                </h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-sm">
                            @forelse ($users as $user)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        {{ $user->username }}
                                        @if(auth()->id() === $user->id)
                                            <span class="ml-2 px-2 py-0.5 text-[10px] font-bold bg-indigo-100 text-indigo-700 rounded-full">YOU</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $roleBadge = match($user->role) {
                                                'superadmin' => 'bg-purple-100 text-purple-800',
                                                'admin'      => 'bg-blue-100 text-blue-800',
                                                'viewer'     => 'bg-gray-100 text-gray-800',
                                                default      => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full uppercase tracking-wider {{ $roleBadge }}">
                                            {{ $user->role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-400">
                                        {{ $user->created_at ? $user->created_at->format('M d, Y h:i A') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <!-- RESET PASSWORD BUTTON -->
                                        <button type="button" data-user="{{ $user }}" onclick="openResetModal(this)" class="text-amber-600 hover:text-amber-900 font-semibold text-xs border border-amber-200 bg-amber-50 px-2.5 py-1 rounded-lg">
                                            Reset Password
                                        </button>

                                        <!-- EDIT ROLE/USERNAME BUTTON -->
                                        <button type="button" data-user="{{ $user }}" onclick="openUserEditModal(this)" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                            Edit
                                        </button>

                                        <!-- DELETE BUTTON -->
                                        @if(auth()->id() !== $user->id)
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Are you sure you want to delete this user account?')" class="text-red-600 hover:text-red-900 font-semibold">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-400">No registered users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. USER ACTIVITY AUDIT LOG TABLE -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center justify-between">
                    <span>Superadmin Activity & Audit Trail</span>
                    <span class="text-xs font-normal text-gray-400">Showing last 20 events</span>
                </h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Performed By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Target User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-sm">
                            @forelse ($logs as $log)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-3">
                                        @php
                                            $actionBadge = match($log->action) {
                                                'CREATE_USER'    => 'bg-green-100 text-green-800',
                                                'UPDATE_USER'    => 'bg-blue-100 text-blue-800',
                                                'RESET_PASSWORD' => 'bg-amber-100 text-amber-800',
                                                'DELETE_USER'    => 'bg-red-100 text-red-800',
                                                default          => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider {{ $actionBadge }}">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 font-medium text-gray-800">{{ $log->user->username ?? 'System' }}</td>
                                    <td class="px-6 py-3 font-semibold text-gray-900">{{ $log->target_user }}</td>
                                    <td class="px-6 py-3 text-xs text-gray-500">{{ $log->details }}</td>
                                    <td class="px-6 py-3 text-xs font-mono text-gray-400">{{ $log->ip_address ?? 'N/A' }}</td>
                                    <td class="px-6 py-3 text-xs text-gray-400">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-400">No activity logs recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- ====================================================== -->
    <!-- EDIT USER MODAL                                        -->
    <!-- ====================================================== -->
    <div id="editUserModal" class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-md z-50 flex items-center justify-center transition-opacity duration-300 opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md mx-4 overflow-hidden transform transition-all duration-300 scale-95" id="editUserModalContainer">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-800">Edit User Details</h3>
                <button type="button" onclick="closeUserEditModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold transition">&times;</button>
            </div>

            <form id="editUserForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Username</label>
                    <input type="text" id="edit_username" name="username" required class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Role</label>
                    <select id="edit_role" name="role" required class="w-full text-sm rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="viewer">Viewer (Read-Only)</option>
                        <option value="admin">Admin (System Manager)</option>
                        <option value="superadmin">Superadmin (Full Access)</option>
                    </select>
                </div>

                <div class="pt-4 flex justify-end space-x-2">
                    <button type="button" onclick="closeUserEditModal()" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium text-sm rounded-xl hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-xl hover:bg-indigo-700 shadow-sm transition">Update Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ====================================================== -->
    <!-- RESET PASSWORD MODAL                                   -->
    <!-- ====================================================== -->
    <div id="resetModal" class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-md z-50 flex items-center justify-center transition-opacity duration-300 opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md mx-4 overflow-hidden transform transition-all duration-300 scale-95" id="resetModalContainer">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-amber-50">
                <h3 class="text-base font-bold text-amber-900">Reset User Password</h3>
                <button type="button" onclick="closeResetModal()" class="text-amber-500 hover:text-amber-700 text-xl font-bold transition">&times;</button>
            </div>

            <form id="resetForm" method="POST" class="p-6 space-y-4">
                @csrf

                <p class="text-xs text-gray-500">
                    You are resetting the password for account: <strong id="reset_username_label" class="text-gray-800"></strong>
                </p>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">New Password</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full text-sm rounded-xl border-gray-200 focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required placeholder="••••••••" class="w-full text-sm rounded-xl border-gray-200 focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div class="pt-4 flex justify-end space-x-2">
                    <button type="button" onclick="closeResetModal()" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium text-sm rounded-xl hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 text-white font-semibold text-sm rounded-xl hover:bg-amber-700 shadow-sm transition">Reset Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT MODAL ANIMATIONS -->
    <script>
        // EDIT MODAL
        function openUserEditModal(btn) {
            const user = JSON.parse(btn.getAttribute('data-user'));
            document.getElementById('editUserForm').action = '/register/users/' + user.id;
            document.getElementById('edit_username').value = user.username || '';
            document.getElementById('edit_role').value = user.role || 'viewer';

            const modal = document.getElementById('editUserModal');
            const container = document.getElementById('editUserModalContainer');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.classList.add('opacity-100');
                container.classList.remove('scale-95');
                container.classList.add('scale-100');
            }, 10);
        }

        function closeUserEditModal() {
            const modal = document.getElementById('editUserModal');
            const container = document.getElementById('editUserModalContainer');
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            container.classList.remove('scale-100');
            container.classList.add('scale-95');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        // RESET PASSWORD MODAL
        function openResetModal(btn) {
            const user = JSON.parse(btn.getAttribute('data-user'));
            document.getElementById('resetForm').action = '/register/users/' + user.id + '/reset-password';
            document.getElementById('reset_username_label').innerText = user.username;

            const modal = document.getElementById('resetModal');
            const container = document.getElementById('resetModalContainer');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.classList.add('opacity-100');
                container.classList.remove('scale-95');
                container.classList.add('scale-100');
            }, 10);
        }

        function closeResetModal() {
            const modal = document.getElementById('resetModal');
            const container = document.getElementById('resetModalContainer');
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            container.classList.remove('scale-100');
            container.classList.add('scale-95');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }
    </script>
</x-app-layout>