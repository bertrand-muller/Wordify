<?php

namespace App\Models\Words;

use Illuminate\Database\Eloquent\Model;

class Word extends Model {

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'words';


    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = ['french', 'english', 'frenchDefinition', 'englishDefinition'];

}
