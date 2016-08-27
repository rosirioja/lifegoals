<?php

namespace App\Repositories;

use Log, DB;

class BaseRepository
{
    protected $modelName;

    /**
     * Map parameters and args values
     *
     * @param  array $params Default parameters
     * @param  array $args Overriding values
     * @return array Parameters with args values
     */
    protected function setParams($params, $args)
    {
        foreach ($args as $i => $value) {
            if (isset($args[$i])) {
                $params[$i] = $value;
            }
        }

        return $params;
    }

    /**
    * Create an instance for the model
    *
    * @access public
    * @return void
    */
    protected function getNewInstance()
    {
        $model = $this->modelName;
        return new $model;
    }

}
