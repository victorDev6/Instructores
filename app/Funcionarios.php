<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Funcionarios extends Model {

    protected $connection = "pgsql";

    protected $table = 'directorio';

}
