<?php

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Model;

class Game extends Model {

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'game';


    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = ['data', 'currentWord', 'playersWord', 'isPrivate'];

}
