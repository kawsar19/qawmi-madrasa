<?php

declare(strict_types=1);

namespace App\Services\Academic;

use App\Models\Academic\AcademicSession;
use RuntimeException;

/**
 * চলতি শিক্ষাবর্ষের container-scoped holder।
 */
class CurrentSession
{
    private ?AcademicSession $session = null;

    public function set(AcademicSession $session): void
    {
        $this->session = $session;
    }

    public function get(): ?AcademicSession
    {
        return $this->session;
    }

    public function id(): ?int
    {
        return $this->session?->id;
    }

    public function getOrFail(): AcademicSession
    {
        return $this->session ?? throw new RuntimeException(
            'কোনো চলতি শিক্ষাবর্ষ নির্ধারণ করা হয়নি।'
        );
    }

    /**
     * ব্যবহারকারী পুরনো (non-current) বর্ষ দেখছে কিনা — UI-তে সতর্কবার্তা
     * দেখানোর জন্য।
     */
    public function isViewingPastSession(): bool
    {
        return $this->session !== null && ! $this->session->is_current;
    }

    public function isLocked(): bool
    {
        return (bool) $this->session?->is_locked;
    }
}
