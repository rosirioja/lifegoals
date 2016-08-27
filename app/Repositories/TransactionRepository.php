<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use App\Contracts\TransactionInterface;
use DB;

class TransactionRepository extends BaseRepository implements TransactionInterface
{
    protected $modelName = 'App\Transaction';

}
