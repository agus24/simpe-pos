<?php
namespace App\Repositories;

use App\Contracts\RepositoryContract;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements RepositoryContract
{
    public function __construct(protected ?Model $model = null) {}

    public function setModel(Model $model) 
    {
        $this->model = $model;
    }

    public static function init(?Model $model = null)
    {
        return new static($model);
    }
}
