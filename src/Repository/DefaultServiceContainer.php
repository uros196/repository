<?php
/**
 * Created by PhpStorm.
 * User: User-x
 * Date: 3/16/2020
 * Time: 3:49 PM
 */

namespace Repository;

use Illuminate\Database\Eloquent\Model;

class DefaultServiceContainer
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    protected function getModel(): Model
    {
        return $this->model;
    }

//    public function findOrFail()
}
