<?php

namespace App\Helpers\ShareUser\Interfaces;

interface ShareUserOptionsInterface
{
    /**
     * Resolve raw share-user records and UI options for a given owner.
     *
     * @return array{records: \Illuminate\Support\Collection, options: \Illuminate\Support\Collection}
     */
    public function resolveForUser(int $userId): array;
}