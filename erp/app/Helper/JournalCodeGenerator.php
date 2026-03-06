<?php

namespace App\Helper;

use App\Models\Journal;
use App\Repositories\JournalRepository;

class JournalCodeGenerator
{
    const MAX_HIERARCHY_DEPTH = 5;

    public function generateAccountCode(?Journal $parent): string
    {
        return $this->generateChildAccountCode($parent);
    }

    protected function generateChildAccountCode(Journal $parent): string
    {
        $lastChildCode = $this->getLastChildCode($parent->id);
        $parentDepth = $this->getCurrentDepth($parent->code);
        if (!$lastChildCode) {
            return $this->formatFirstChildCode($parent->code, $parentDepth);
        }

        $nextNumber = $this->extractAndIncrementSegment($lastChildCode, $parent->code);

        return $this->formatNewChildCode($parent->code, $nextNumber, $parentDepth + 1);
    }

    protected function formatFirstChildCode(string $parentCode, int $parentDepth): string
    {
        // First level children (depth 2) get single digit
        if ($parentDepth === 1) {
            return $parentCode . '1';
        }
        // Deeper levels get 2 digits
        return $parentCode . '01';
    }

    protected function formatNewChildCode(string $parentCode, int $nextNumber, int $newDepth): string
    {
        // First level children (depth 2) get single digit
        if ($newDepth === 2) {
            return $parentCode . $nextNumber;
        }
        // Deeper levels get 2 digits
        return $parentCode . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    }

    protected function extractAndIncrementSegment(string $fullCode, string $prefix): int
    {
        $segment = substr($fullCode, strlen($prefix));

        if ($segment === false || $segment === '') {
            return 1;
        }

        return (int)$segment + 1;
    }

    protected function getCurrentDepth(string $code): int
    {
        if (strlen($code) === 1) return 1; // Root level

        $depth = 1;
        $pos = 0;
        $length = strlen($code);

        while ($pos < $length) {
            $segmentLength = ($depth === 1) ? 1 : 2;
            $pos += $segmentLength;
            $depth++;
        }

        return $depth - 1;
    }


    protected function getLastChildCode(int $parentId): ?string
    {
        return app(JournalRepository::class)->getLastChildCode($parentId);
    }

}
