<?php
namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface RepositoryContract {
    public static function init(Model $model);
}
