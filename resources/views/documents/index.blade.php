<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Document Tracking</h2>
                <p class="mt-0.5 text-xs text-gray-400">Maintain a single register for operational records and their review lifecycle.</p>
            </div>
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">{{ $documents->count() }} records</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto w-full max-w-[98%] space-y-6 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif

            <div class="grid gap-4 sm:grid-cols-4">
                @foreach(['Pending' => 'amber', 'In Review' => 'blue', 'Approved' => 'emerald', 'Archived' => 'slate'] as $status => $color)
                    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">{{ $status }}</p>
                        <p class="mt-2 text-3xl font-semibold text-{{ $color }}-600">{{ $documentCounts[$status] ?? 0 }}</p>
                    </div>
                @endforeach
            </div>

            @if(auth()->user()->isAdmin())
                <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    @csrf
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h3 class="font-semibold text-gray-800">Add document</h3>
                            <p class="mt-0.5 text-xs text-gray-400">Record metadata and attach the working file.</p>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700">Admin entry</span>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div class="lg:col-span-2"><label class="text-xs font-semibold text-gray-600">Document title</label><input name="title" required value="{{ old('title') }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. VAPT Completion Report"></div>
                        <div><label class="text-xs font-semibold text-gray-600">Category</label><input name="category" required value="{{ old('category') }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Assessment, memo..."></div>
                        <div><label class="text-xs font-semibold text-gray-600">Status</label><select name="status" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option>Pending</option><option>In Review</option><option>Approved</option><option>Archived</option></select></div>
                        <div data-review-office-field class="hidden">
                            <label class="text-xs font-semibold text-gray-600">Review office</label>
                            <select name="review_office" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                                <option value="">Select review office</option>
                                @foreach(['OIC ASDB', 'OIC SMSB', 'OIC ADMIN', 'DUTY OFFICER', 'EX-O, ISG', 'CO, ISG'] as $office)
                                    <option value="{{ $office }}" @selected(old('review_office') === $office)>{{ $office }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="text-xs font-semibold text-gray-600">Owner</label><input name="owner" value="{{ old('owner') }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Person or unit"></div>
                        <div><label class="text-xs font-semibold text-gray-600">Due date</label><input type="date" name="due_date" value="{{ old('due_date') }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></div>
                        <div class="lg:col-span-2"><label class="text-xs font-semibold text-gray-600">Attachment</label><input type="file" name="file" class="mt-1 block w-full rounded-lg border border-gray-300 p-2 text-sm text-gray-600"></div>
                        <div class="lg:col-span-3"><label class="text-xs font-semibold text-gray-600">Description</label><input name="description" value="{{ old('description') }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Optional context or notes"></div>
                        <div class="flex items-end"><button class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">Add document</button></div>
                    </div>
                </form>
            @endif

            <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(22rem,0.42fr)]">
                <div class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <form method="GET" class="flex flex-col gap-3 border-b border-gray-100 p-5 sm:flex-row">
                    <input name="search" value="{{ request('search') }}" class="w-full rounded-lg border-gray-300 text-sm sm:flex-1" placeholder="Search title, category, or owner">
                    <select name="status" class="rounded-lg border-gray-300 text-sm"><option value="">All statuses</option>@foreach(['Pending', 'In Review', 'Approved', 'Archived'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select>
                    <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Filter</button>
                </form>
                <div class="overflow-x-auto overflow-y-visible">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach(['Document', 'Category', 'Owner', 'Due date', 'Status', 'Attachment', 'Updated'] as $heading)
                                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $heading }}</th>
                                @endforeach
                                @if(auth()->user()->isAdmin())
                                    <th class="px-5 py-3"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse($documents as $document)
                                <tr class="hover:bg-gray-50">
                                    <td class="max-w-xs px-5 py-4"><p class="font-semibold text-gray-900">{{ $document->title }}</p><p class="mt-1 truncate text-xs text-gray-500">{{ $document->description ?: 'No description' }}</p></td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $document->category }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $document->owner ?: 'Unassigned' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $document->due_date?->format('M d, Y') ?: 'No deadline' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ match($document->status) { 'Approved' => 'bg-emerald-100 text-emerald-800', 'In Review' => 'bg-blue-100 text-blue-800', 'Archived' => 'bg-slate-100 text-slate-700', default => 'bg-amber-100 text-amber-800' } }}">{{ $document->status }}</span></td>
                                    <td class="whitespace-nowrap px-5 py-4">@if($document->file_path)<button type="button" data-view-url="{{ route('documents.view', $document) }}?preview={{ $document->updated_at->timestamp }}" data-view-name="{{ $document->file_name }}" class="font-semibold text-blue-700 hover:underline">View</button><span class="mx-1 text-gray-300">|</span><a href="{{ route('documents.download', $document) }}" class="font-semibold text-emerald-700 hover:underline">Download</a><span class="ml-1 text-xs text-gray-400">{{ Str::limit($document->file_name, 20) }}</span>@else<span class="text-gray-400">None</span>@endif</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-xs text-gray-500">{{ $document->updated_at->format('M d, Y') }}</td>
                                    @if(auth()->user()->isAdmin())
                                        <td class="whitespace-nowrap px-5 py-4 text-right">
                                            <button type="button" data-manage-open="manage-document-{{ $document->id }}" class="cursor-pointer text-xs font-semibold text-emerald-700 hover:text-emerald-800">Manage</button>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400">No documents match the current filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                </div>

                @foreach($documents as $document)
                    @if(auth()->user()->isAdmin())
                        <div id="manage-document-{{ $document->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/20 p-4 backdrop-blur-sm">
                            <div class="w-full max-w-2xl scale-95 rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl transition duration-200 ease-out animate-[modal-in_0.2s_ease-out_forwards]">
                                <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3">
                                    <div>
                                        <h4 class="text-base font-semibold text-gray-800">Edit document</h4>
                                        <p class="text-xs text-gray-400">Update the current record and file details.</p>
                                    </div>
                                    <button type="button" data-manage-close="manage-document-{{ $document->id }}" class="text-xs font-semibold text-gray-500 hover:text-gray-700">Close</button>
                                </div>
                                <form method="POST" action="{{ route('documents.update', $document) }}" enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div class="sm:col-span-2"><label class="text-xs font-semibold text-gray-600">Document title</label><input name="title" required value="{{ $document->title }}" class="mt-1 w-full rounded-lg border-gray-300 text-xs"></div>
                                        <div><label class="text-xs font-semibold text-gray-600">Category</label><input name="category" required value="{{ $document->category }}" class="mt-1 w-full rounded-lg border-gray-300 text-xs"></div>
                                        <div><label class="text-xs font-semibold text-gray-600">Status</label><select name="status" class="mt-1 w-full rounded-lg border-gray-300 text-xs">@foreach(['Pending', 'In Review', 'Approved', 'Archived'] as $status)<option @selected($document->status === $status)>{{ $status }}</option>@endforeach</select></div>
                                        <div data-review-office-field class="{{ $document->status === 'In Review' ? '' : 'hidden' }} sm:col-span-2"><label class="text-xs font-semibold text-gray-600">Review office</label><select name="review_office" class="mt-1 w-full rounded-lg border-gray-300 text-xs"><option value="">Select review office</option>@foreach(['OIC ASDB', 'OIC SMSB', 'OIC ADMIN', 'DUTY OFFICER', 'EX-O, ISG', 'CO, ISG'] as $office)<option value="{{ $office }}" @selected($document->review_office === $office)>{{ $office }}</option>@endforeach</select></div>
                                        <div><label class="text-xs font-semibold text-gray-600">Owner</label><input name="owner" value="{{ $document->owner }}" class="mt-1 w-full rounded-lg border-gray-300 text-xs" placeholder="Owner"></div>
                                        <div><label class="text-xs font-semibold text-gray-600">Due date</label><input type="date" name="due_date" value="{{ $document->due_date?->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border-gray-300 text-xs"></div>
                                        <div class="sm:col-span-2"><label class="text-xs font-semibold text-gray-600">Description</label><input name="description" value="{{ $document->description }}" class="mt-1 w-full rounded-lg border-gray-300 text-xs" placeholder="Description"></div>
                                        <div class="sm:col-span-2"><label class="text-xs font-semibold text-gray-600">Replace file</label><input type="file" name="file" class="mt-1 w-full text-xs"></div>
                                    </div>
                                    <div class="mt-5 flex items-center justify-end gap-2 border-t border-gray-100 pt-4">
                                        <button type="button" data-manage-close="manage-document-{{ $document->id }}" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Save changes</button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Delete this document record?')" class="mt-4 border-t border-gray-100 pt-4">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800">Delete record</button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endforeach

                <aside class="flex min-h-[32rem] flex-col rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                        <div>
                            <h3 class="font-semibold text-gray-800">Document viewer</h3>
                            <p id="viewerName" class="mt-0.5 max-w-[16rem] truncate text-xs text-gray-400">Select View to preview an attachment.</p>
                        </div>
                        <a id="viewerFallback" href="#" target="_blank" rel="noopener" class="hidden text-xs font-semibold text-emerald-700 hover:underline">Open separately</a>
                    </div>
                    <div class="relative flex-1 bg-slate-50">
                        <div id="viewerEmpty" class="absolute inset-0 flex items-center justify-center px-8 text-center text-sm text-gray-400">Choose a document attachment from the register.</div>
                        <iframe id="documentViewer" title="Document preview" class="hidden h-full min-h-[28rem] w-full border-0 bg-white"></iframe>
                    </div>
                </aside>
            </section>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const viewer = document.getElementById('documentViewer');
            const emptyState = document.getElementById('viewerEmpty');
            const viewerName = document.getElementById('viewerName');
            const fallback = document.getElementById('viewerFallback');

            document.querySelectorAll('[data-view-url]').forEach(function (button) {
                button.addEventListener('click', function () {
                    viewer.src = button.dataset.viewUrl;
                    viewerName.textContent = button.dataset.viewName;
                    fallback.href = button.dataset.viewUrl;
                    fallback.classList.remove('hidden');
                    emptyState.classList.add('hidden');
                    viewer.classList.remove('hidden');
                });
            });

            document.querySelectorAll('[data-manage-open]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const modalId = button.dataset.manageOpen;
                    const modal = document.getElementById(modalId);
                    const modalCard = modal?.querySelector('div > div');

                    if (modal) {
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                        requestAnimationFrame(function () {
                            if (modalCard) {
                                modalCard.classList.remove('scale-95');
                                modalCard.classList.add('scale-100');
                            }
                        });
                    }
                });
            });

            document.querySelectorAll('[data-manage-close]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const modalId = button.dataset.manageClose;
                    const modal = document.getElementById(modalId);
                    const modalCard = modal?.querySelector('div > div');

                    if (modal) {
                        if (modalCard) {
                            modalCard.classList.add('scale-95');
                            modalCard.classList.remove('scale-100');
                        }
                        setTimeout(function () {
                            modal.classList.add('hidden');
                            modal.classList.remove('flex');
                        }, 150);
                    }
                });
            });

            document.querySelectorAll('.fixed.inset-0.z-50').forEach(function (modal) {
                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        const modalCard = modal.querySelector('div > div');
                        if (modalCard) {
                            modalCard.classList.add('scale-95');
                            modalCard.classList.remove('scale-100');
                        }
                        setTimeout(function () {
                            modal.classList.add('hidden');
                            modal.classList.remove('flex');
                        }, 150);
                    }
                });
            });

            document.querySelectorAll('select[name="status"]').forEach(function (statusSelect) {
                const form = statusSelect.closest('form');
                const reviewField = form?.querySelector('[data-review-office-field]');
                const reviewSelect = form?.querySelector('select[name="review_office"]');

                function toggleReviewOfficeField() {
                    if (!reviewField || !reviewSelect) {
                        return;
                    }

                    const showField = statusSelect.value === 'In Review';
                    reviewField.classList.toggle('hidden', !showField);
                    reviewSelect.required = showField;

                    if (!showField) {
                        reviewSelect.value = '';
                    }
                }

                statusSelect.addEventListener('change', toggleReviewOfficeField);
                toggleReviewOfficeField();
            });
        });
    </script>
</x-app-layout>