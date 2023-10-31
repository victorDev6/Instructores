<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tokens_icti extends Model {

    protected $connection = "pgsql";

    protected $table = 'tokens_icti';

    protected $fillable = ['token'];

}
