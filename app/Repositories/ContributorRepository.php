<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use App\Contracts\ContributorInterface;
use DB;

class ContributorRepository extends BaseRepository implements ContributorInterface
{
    protected $modelName = 'App\Contributor';

}
