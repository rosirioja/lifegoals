<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use App\Contracts\UserInterface;
use DB;

class UserRepository extends BaseRepository implements UserInterface
{
    protected $modelName = 'App\User';

}
