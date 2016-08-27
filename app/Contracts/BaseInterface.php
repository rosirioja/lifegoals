<?php

namespace App\Contracts;

interface BaseInterface
{
	public function exists(array $fieldValue, $id = 0);

    public function get($id = 0, $columns = array('*'));
    public function getBy(array $where, $select = ['*'], $join = []);
    public function getList(array $args);
    public function getTotalBy($where = array(), $join = []);

    public function store(array $data);

    public function update($id = 0, array $data);
}
