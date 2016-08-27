<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use App\Contracts\UserAccountInterface;
use DB;

class UserAccountRepository extends BaseRepository implements UserAccountInterface
{
    protected $modelName = 'App\UserAccount';

}
