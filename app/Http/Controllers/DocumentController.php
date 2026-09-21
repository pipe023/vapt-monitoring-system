<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $portalUser = $request->user();
        $documentUser = $this->resolveDocumentTrackingUser($request);

        if (! $portalUser || ! session('document_tracking_session') || ! $documentUser) {
            return view('documents.index', [
                'documents' => collect(),
                'documentCounts' => collect(),
                'showDocumentLogin' => true,
                'documentTrackingUser' => null,
            ]);
        }

        $query = Document::query()->with('user');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('owner', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if (! $documentUser->canViewAllDocuments()) {
            $query->whereHas('user', function ($builder) use ($documentUser) {
                $builder->where('role', $documentUser->role);
            });
        }

        $documents = $query->latest()->get();
        $documentCounts = $documents->groupBy('status')->map->count();

        return view('documents.index', [
            'documents' => $documents,
            'documentCounts' => $documentCounts,
            'showDocumentLogin' => false,
            'documentTrackingUser' => $documentUser,
        ]);
    }

    public function store(Request $request)
    {
        $documentUser = $this->resolveDocumentTrackingUser($request);
        $this->authorizeDocumentAccess($documentUser);

        $validated = $this->validated($request);
        $validated['user_id'] = $documentUser->id;

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('documents', 'local');
            $validated['file_name'] = $request->file('file')->getClientOriginalName();
        }

        Document::create($validated);

        return redirect()->route('documents.index')->with('success', 'Document added successfully.');
    }

    public function update(Request $request, Document $document)
    {
        $documentUser = $this->resolveDocumentTrackingUser($request);
        $this->authorizeDocumentAccess($documentUser, $document);

        $validated = $this->validated($request);

        if ($request->hasFile('file')) {
            Storage::disk('local')->delete($document->file_path);
            $validated['file_path'] = $request->file('file')->store('documents', 'local');
            $validated['file_name'] = $request->file('file')->getClientOriginalName();
        }

        $document->update($validated);

        return redirect()->route('documents.index')->with('success', 'Document updated successfully.');
    }

    public function destroy(Request $request, Document $document)
    {
        $documentUser = $this->resolveDocumentTrackingUser($request);
        $this->authorizeDocumentAccess($documentUser, $document);

        if ($document->file_path) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Document deleted successfully.');
    }

    public function download(Request $request, Document $document)
    {
        $documentUser = $this->resolveDocumentTrackingUser($request);
        $this->authorizeDocumentAccess($documentUser, $document);
        abort_unless($document->file_path && Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    public function view(Request $request, Document $document)
    {
        $documentUser = $this->resolveDocumentTrackingUser($request);
        $this->authorizeDocumentAccess($documentUser, $document);
        abort_unless($document->file_path && Storage::disk('local')->exists($document->file_path), 404);

        $disk = Storage::disk('local');
        $mimeType = $disk->mimeType($document->file_path) ?: 'application/octet-stream';

        if ($mimeType !== 'application/pdf' && ! str_starts_with($mimeType, 'image/')) {
            return $this->officePreview($disk->path($document->file_path), $document->file_name);
        }

        return response()->file($disk->path($document->file_path), [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . addslashes($document->file_name) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function officePreview(string $path, string $fileName)
    {
        $zip = new \ZipArchive();
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $content = [];

        if ($zip->open($path) !== true) {
            return $this->previewMessage('This document could not be opened for preview.');
        }

        if ($extension === 'doc' || $extension === 'docx') {
            $xml = $zip->getFromName('word/document.xml');
            $content = $this->xmlParagraphs($xml);
        } elseif ($extension === 'ppt' || $extension === 'pptx') {
            for ($index = 1; $index <= $zip->numFiles; $index++) {
                $entry = $zip->getNameIndex($index - 1);
                if (preg_match('#^ppt/slides/slide\d+\.xml$#', $entry)) {
                    $content[] = implode(' ', $this->xmlText($zip->getFromName($entry)));
                }
            }
        } elseif ($extension === 'xls' || $extension === 'xlsx') {
            $sharedStrings = $this->xmlText($zip->getFromName('xl/sharedStrings.xml'));
            $worksheet = $zip->getFromName('xl/worksheets/sheet1.xml');
            $content = $this->xmlRows($worksheet, $sharedStrings);
        }

        $zip->close();

        return response()->view('documents.preview', [
            'fileName' => $fileName,
            'content' => array_values(array_filter($content, fn ($line) => trim($line) !== '')),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    private function xmlParagraphs(?string $xml): array
    {
        if (! $xml) {
            return [];
        }

        $document = simplexml_load_string($xml);
        return $document ? array_map(fn ($paragraph) => trim((string) $paragraph), $document->xpath('//*[local-name()="p"]')) : [];
    }

    private function xmlText(?string $xml): array
    {
        if (! $xml) {
            return [];
        }

        $document = simplexml_load_string($xml);
        return $document ? array_map(fn ($text) => (string) $text, $document->xpath('//*[local-name()="t"]')) : [];
    }

    private function xmlRows(?string $xml, array $sharedStrings): array
    {
        if (! $xml) {
            return [];
        }

        $document = simplexml_load_string($xml);
        if (! $document) {
            return [];
        }

        $rows = [];
        foreach ($document->xpath('//*[local-name()="row"]') as $row) {
            $cells = [];
            foreach ($row->xpath('./*[local-name()="c"]') as $cell) {
                $value = (string) ($cell->v ?? '');
                $type = (string) ($cell['t'] ?? '');
                $cells[] = $type === 's' ? ($sharedStrings[(int) $value] ?? '') : $value;
            }
            $rows[] = implode(' | ', $cells);
        }

        return $rows;
    }

    private function previewMessage(string $message)
    {
        return response()->view('documents.preview', [
            'fileName' => '',
            'content' => [$message],
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    private function resolveDocumentTrackingUser(Request $request): ?User
    {
        $userId = $request->session()->get('document_tracking_user_id');

        return $userId ? User::find($userId) : null;
    }

    private function authorizeDocumentAccess(?\App\Models\User $user, ?Document $document = null): void
    {
        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        if ($user->canViewAllDocuments()) {
            return;
        }

        if (! $user->canAccessDocumentTracking()) {
            abort(403, 'You do not have access to the document tracking module.');
        }

        if ($document && $document->user && $document->user->role !== $user->role) {
            abort(403, 'This document belongs to another office role.');
        }
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'status' => 'required|in:Pending,In Review,Approved,Archived',
            'review_office' => 'nullable|string|max:255|required_if:status,In Review',
            'owner' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string|max:5000',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:10240',
        ]);

        if (($validated['status'] ?? null) !== 'In Review') {
            $validated['review_office'] = null;
        }

        return $validated;
    }
}