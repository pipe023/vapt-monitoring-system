<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Collection;

class DocumentDeadlineAlerts
{
    public function forUser(User $user): Collection
    {
        if (! $user->canAccessDocumentTracking()) {
            return collect();
        }

        $today = today();

        return Document::query()
            ->whereIn('status', ['Pending', 'In Review'])
            ->whereNull('completed_at')
            ->whereDate('due_date', '<=', $today->copy()->addDays(3))
            ->when(! $user->canViewAllDocuments(), fn ($query) => $query
                ->whereHas('user', fn ($owner) => $owner->where('role', $user->role)))
            ->orderBy('due_date')->orderBy('id')->get()
            ->map(function (Document $document) use ($today) {
                $days = (int) $today->diffInDays($document->due_date, false);
                $label = match (true) {
                    $days < 0 => abs($days).' day'.(abs($days) === 1 ? '' : 's').' overdue',
                    $days === 0 => 'Due today',
                    $days === 1 => 'Due tomorrow',
                    default => 'Due in '.$days.' days',
                };

                return [
                    'id' => $document->id,
                    'key' => $document->id.':'.$document->due_date->toDateString().':'.$today->toDateString(),
                    'title' => $document->title,
                    'due_date' => $document->due_date->toDateString(),
                    'label' => $label,
                    'overdue' => $days < 0,
                    'url' => route('documents.index', ['search' => $document->title]),
                ];
            });
    }
}
