<?php


namespace App\Models\Words;

use Illuminate\Database\Eloquent\Model;

class WordDatas extends Model {

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'word_datas';


    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = ['word','datas'];

}