<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use App\Contracts\GoalInterface;
use DB;

class GoalRepository extends BaseRepository implements GoalInterface
{
    protected $modelName = 'App\Goal';

}
