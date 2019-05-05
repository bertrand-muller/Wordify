<?php

use Database\traits\TruncateTable;
use Database\traits\DisableForeignKeys;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WordSeeder extends Seeder
{
    use DisableForeignKeys, TruncateTable;

    /**
     * Run the database seed.
     *
     * @return void
     */
    public function run()
    {
        $this->disableForeignKeys();
        $this->truncate('roles');

        $words = [
            'game',
            'car',
            'plane',
            'water',
            'school',
            'house',
            'word',
            'fly',
            'fire',
            'police',
            'blood',
            'orange'
        ];

        $wordsToInsert = [];
        foreach ($words as $word){
            $wordsToInsert[] = [
                'word' => ucfirst(strtolower($word)),
                'valid' => true,
                'userId' => 0
            ];
        }

        DB::table('words')->insert($wordsToInsert);

        $this->enableForeignKeys();
    }
}