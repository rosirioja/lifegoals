<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class BaseController extends Controller
{
    public $goal_status = [
        'ongoing' => 'ONGOING',
        'achieved' => 'ACHIEVED'
    ];

    /**
     * Check if the goal status is achieved
     *
     * @param array goal details
     * @param float new accumulated amount
     * @return boolean
     */
    public function checkGoalStatus($goal = [], $new_amount = '')
    {
        try {
            if (empty($goal)) {
                throw new Exception();
            }

            $accumulated_amount = empty($new_amount) ? $goal->accumulated_amount : $new_amount;

            if ($accumulated_amount >= $goal->target_amount) {
                return true;
            }

            if (date('Y-m-d') >= date('Y-m-d', strtotime($goal->target_date))) {
                return true;
            }

        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
