<?php


namespace App\Models\Words;


use Illuminate\Database\Eloquent\Model;

class Counter extends Model {

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'word_counter';


    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = ['word','day','counter'];

}