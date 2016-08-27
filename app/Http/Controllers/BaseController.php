<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Contracts\GoalInterface;

use Log, DB, Exception;

class BaseController extends Controller
{
    public $goal_status = [
        'ongoing' => 'ONGOING',
        'achieved' => 'ACHIEVED'
    ];

    public function __construct(
        GoalInterface $goalInterface)
    {
        $this->goal = $goalInterface;

        DB::enableQueryLog();
    }

    /**
     * Check if the goal status is achieved
     *
     * @param array goal details
     * @param float new accumulated amount
     * @return boolean
     */
    public function checkGoalStatus($goal = [], $new_amount = '')
    {
        if (empty($goal)) {
            return false;
        }

        $accumulated_amount = empty($new_amount) ? $goal->accumulated_amount : $new_amount;

        if ($accumulated_amount >= $goal->target_amount == 1) {
            return true;
        }

        if ( ($goal->target_date != null) && (date('Y-m-d') >= date('Y-m-d', strtotime($goal->target_date))) ) {
            return true;
        }

        return false;
    }

    /**
     * Update Goal Status to Achieved
     *
     * @param int goal id
     * @return boolean
     */
    public function updateGoalAchievedStatus($goal_id = '')
    {
        try {
            if (empty($goal_id)) {
                throw new Exception();
            }

            $data = [
                'status' => $this->goal_status['achieved'],
                'achieved_date' => date('Y-m-d')
            ];

            if (! $this->goal->update($goal_id, $data)) {
                throw new Exception();
            }

        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
