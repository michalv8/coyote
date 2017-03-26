<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Guest;
use Coyote\Repositories\Contracts\GuestRepositoryInterface;

class GuestRepository extends Repository implements GuestRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Guest::class;
    }
}
