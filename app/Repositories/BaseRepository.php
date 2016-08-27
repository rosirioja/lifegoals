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

     /**
     * Checks if table record already exists.
     *
     * @param  array|string $where Where clause
     * @param int | id
     * @return boolean True if record already exists, false for non-existent record
     */
    public function exists(array $where, $id = 0)
    {
        $instance = $this->getNewInstance();
        $instance = $instance->where($where);

        if ($id != 0) $instance = $instance->where('id', '!=', $id);

        return $instance->exists();
    }

    /**
    * Get table record by primary key
    *
    * @access public
    * @params int | $id
    * @params array | columns
    * @return void
    */
    public function get($id = 0, $columns = array('*'))
    {
        return $this->getNewInstance()->select($columns)->find($id);
    }

    /**
     * Get table record using a generic where clause
     *
     * @param  array|string $where Where clause
     * @param  string $select Fields to include in select
     * @return object Table record
     */
    public function getBy(array $where, $select = ['*'], $join = [])
    {
        $instance = $this->getNewInstance();
        $instance = $instance->select($select);

        if ( ! empty($where)) {
            foreach($where as $field => $value) {
                $instance = $instance->where($field, $value);
            }
        }

        if ( ! empty($join))
        {
            foreach ($join as $row)
            {
                if (empty($row['operator'])) $row['operator'] = '=';
                if (empty($row['join'])) $row['join'] = '';

                switch ($row['join']) {
                    case 'left':
                        $instance = $instance->leftJoin($row['table'], $row['one'], $row['operator'], $row['two']);
                        break;

                    default:
                        $instance = $instance->join($row['table'], $row['one'], $row['operator'], $row['two']);
                        break;
                }
            }
        }

        return $instance->get()->first();
    }
    
    /**
    * Insert data in the database
    *
    * @access public
    * @params array | data
    * @return void
    */
    public function store(array $data)
    {
        $instance = $this->getNewInstance();
        return $instance->create($data);
    }

    /**
    * Update record by using primary id
    *
    * @access public
    * @params int | id
    * @params array | data
    * @return void
    */
    public function update($id = 0, array $data)
    {
        $instance = $this->getNewInstance()->find($id);
        return $instance->update($data);
    }
}
