<?php

namespace App\Repositories;

abstract class BaseRepository implements BaseRepositoryInterface
{

    protected $model;

 /*
    ** Interface methods implemented
    */
    public function all(){ return $this->model->all(); }

    public function find($id) { return $this->model->find($id); }

    public function create(array $data) { return $this->model->create($data); }

    public function update($id, array $data)
    {
        $modelInstance = $this->model->findOrFail($id);
        if ($modelInstance) {
            $modelInstance->update($data);
        }
        return $modelInstance;
    }

    public function delete($id)
    {
        $modelInstance = $this->model->findOrFail($id);
        if ($modelInstance) {
            $modelInstance->delete();
        }
        return $modelInstance;
    }
}