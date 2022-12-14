<?php
namespace App\Http\Services;


class CommonService
{

//    public $model = null;
//    public $repository = null;
//    public $object = null;

    public function __construct($model, $repo)
    {
        $this->model = $model;
        $this->repository = $repo;
        $this->object = new $this->repository($this->model);
    }

    public function insert($entity)
    {
        return $this->object->insert($entity);
    }
    public function createData($entity)
    {
        return $this->object->create($entity);
    }

    public function getAll()
    {
        return $this->object->getAll();
    }

    public function getById($id)
    {
        return $this->object->getById($id);
    }

    public function getDocs($params)
    {
        return $this->object->getDocs($params);
    }

    public function delete($id)
    {
        return $this->object->deleteById($id);
    }

    public function update($where, $update){
        return $this->object->update($where, $update);
    }

    public function deleteWhere($where, $isForce = false){
        return $this->object->deleteWhere($where, $isForce);
    }

    public function exists($where = [], $relation = [])
    {
        return $this->object->exists($where,$relation);
    }
    public function countWhere($where = [], $relation = [])
    {
        return $this->object->countWhere($where,$relation);
    }

    public function randomWhere($quantity, $where = [], $relation = [])
    {
        return $this->object->randomWhere($quantity,$where,$relation);
    }

    public function whereFirst($where = [], $relation = [])
    {
        return $this->object->whereFirst($where,$relation);
    }

    public function selectWhere($select, $where, $relation = [], $paginate = 0)
    {
        return $this->object->selectWhere($select, $where, $relation, $paginate);
    }

    public function limitWhere($quantity, $where = [], $relation = [])
    {
        return $this->object->limitWhere($quantity, $where, $relation);
    }
}
