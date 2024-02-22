<?php

namespace App\Repositories;

use App\Models\ShareUser;
use App\Repositories\Interfaces\ShareUserRepositoryInterface;

class ShareUserRepository extends AppRepository implements ShareUserRepositoryInterface
{
    public function __construct(?ShareUser $shareUser = null)
    {
        parent::__construct($shareUser ?? new ShareUser);
    }
}
