<?php

use Database\traits\TruncateTable;
use Database\traits\DisableForeignKeys;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WordDatasSeeder extends Seeder
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


        foreach ($words as $word){
            DB::table('word_datas')->insert([
                'word' => $word[0],
                'datas' => $word[1]
            ]);
        }

        $this->enableForeignKeys();
    }
}