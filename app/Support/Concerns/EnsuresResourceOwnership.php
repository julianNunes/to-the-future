<?php

namespace App\Support\Concerns;

use Illuminate\Auth\Access\AuthorizationException;

trait EnsuresResourceOwnership
{
    protected function ensureOwnedByCurrentUser(mixed $resource, string $ownerKey = 'user_id'): void
    {
        $ownerId = data_get($resource, $ownerKey);

        if (! auth()->check() || $ownerId === null || (int) $ownerId !== (int) auth()->id()) {
            throw new AuthorizationException();
        }
    }
}
