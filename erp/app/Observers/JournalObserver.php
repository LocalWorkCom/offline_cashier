<?php

namespace App\Observers;

use App\Models\Journal;
use App\Models\JournalLog;

class JournalObserver
{
    public function created(Journal $journal)
    {
        $this->createJournalLog($journal, 'created');
    }

    public function updated(Journal $journal)
    {
        // Only log if there are actual changes (not just timestamps)
        if ($journal->wasChanged()) {
            $changes = $this->getChangedValues($journal);

            if (!empty($changes)) {
                $this->createJournalLog($journal, 'updated', $changes);
            }
        }
    }

    public function deleted(Journal $journal)
    {
        $this->createJournalLog($journal, 'deleted');
    }

    private function createJournalLog(Journal $journal, string $action, array $changes = null)
    {
        JournalLog::create([
            'journal_id' => $journal->id,
            'action' => $action,
            'log_values' => $changes ?? $journal->getAttributes(),
            'log_timestamp' => now(),
            'created_by' => auth('employee')->id(),
        ]);
    }

    private function getChangedValues(Journal $journal): array
    {
        $changes = [];
        $original = $journal->getOriginal();

        foreach ($journal->getChanges() as $key => $newValue) {
            // Skip timestamps and the updated_by field
            if (!in_array($key, ['updated_at', 'modified_by'])) {
                $changes[$key] = [
                    'old' => $original[$key] ?? null,
                    'new' => $newValue
                ];
            }
        }

        return $changes;
    }
}
