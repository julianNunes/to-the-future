<?php

namespace App\Helpers\ShareUser;

use App\Helpers\ShareUser\Interfaces\ShareUserOptionsInterface;
use App\Repositories\Interfaces\ShareUserRepositoryInterface;
use Illuminate\Support\Collection;

class ShareUserOptions implements ShareUserOptionsInterface
{
    public function __construct(private ShareUserRepositoryInterface $shareUserRepository)
    {
    }

    public function resolveForUser(int $userId): array
    {
        $records = $this->shareUserRepository->get(['user_id' => $userId], [], [], ['shareUser']);

        return [
            'records' => $records,
            'options' => $this->mapOptions($records),
        ];
    }

    private function mapOptions(Collection $shareUsers): Collection
    {
        if (!$shareUsers->count()) {
            return collect();
        }

        return $shareUsers->map(function ($item) {
            return [
                'share_user_id' => $item->share_user_id,
                'share_user_name' => $item->shareUser->name,
            ];
        });
    }
}
