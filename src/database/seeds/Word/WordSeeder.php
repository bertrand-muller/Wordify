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
            'animal', 'bird', 'dog', 'fish', 'food', 'horse', 'tail', 'light', 
            'sun', 'snow', 'boat', 'car', 'door', 'course', 'center', 'city', 
            'farm', 'home', 'house', 'room', 'bed', 'plane', 'road', 'way', 
            'street', 'surface', 'school', 'town', 'travel', 'port', 'tree', 
            'sea', 'ship', 'water', 'river', 'land', 'island', 'moon', 'mountain', 
            'plant', 'star', 'wind', 'fire', 'wood', 'ground', 'boy', 'child',
            'family', 'friend', 'girl', 'group', 'man', 'men', 'mother', 'people',
            'person', 'king', 'body', 'eye', 'face', 'hand', 'head', 'foot',
            'foot', 'age', 'world', 'year', 'West', 'Earth', 'North', 'East', 'country', 
            'South', 'day', 'morning', 'night', 'thing', 'book', 'box', 'class',
            'color', 'example', 'field', 'form', 'line', 'machine', 'map', 'music', 'noun', 
            'figure', 'air', 'area', 'base', 'number', 'game', 'list', 'object', 'order', 
            'paper', 'letter', 'story', 'pattern', 'picture', 'piece', 'towel', 'place', 
            'point', 'name', 'word', 'round', 'rule', 'science', 'sentence', 'song', 'sound', 
            'state', 'step', 'table', 'test', 'power', 'product', 'part', 'voice', 'war', 'wheel', 
            'work', 'measure', 'mile', 'inch', 'time', 'hour', 'minute', 'pound', 'money', 'week', 
            'unit', 'shape', 'side', 'size', 'beauty', 'problem', 'notice', 'question', 'thought', 
            'mind', 'heat', 'help', 'idea', 'interest', 'love', 'force', 'end', 'fact', 'blue', 
            'black', 'dark', 'gold', 'green', 'red', 'white', 'five', 'four', 'first', 'hundred', 
            'second', 'six', 'ten', 'thousand', 'three', 'best', 'better', 'big', 'large', 
            'short', 'little', 'long', 'old', 'young', 'busy', 'clear', 'common', 'complete', 
            'correctable', 'deep', 'direct', 'done', 'dry', 'far', 'fast', 'few', 'final', 'fine', 
            'free', 'full', 'good', 'great', 'half', 'hard', 'hot', 'kind', 'last', 'late', 'less', 
            'low', 'new', 'next', 'numeral', 'other', 'plain', 'possible', 'quick', 'ready', 'real', 
            'rich', 'right', 'same', 'several', 'simple', 'slow', 'small', 'special', 'strong', 'top', 
            'usual', 'warm', 'well', 'whole', 'act',
            'add', 'answer', 'appear', 'ask', 'begin', 'bring', 'build', 'call',
            'come', 'care', 'carry', 'cause', 'change', 'check', 'close', 'contain', 'cover',
            'cross', 'cry', 'cut', 'decide', 'develop', 'differ', 'draw', 'drive', 'ease',
            'eat', 'fall', 'feel', 'fill', 'find', 'fly', 'follow', 'give',
            'govern', 'grow', 'happen', 'hear', 'help', 'hold', 'keep', 'know', 'laugh', 'lay',
            'lead', 'learn', 'leave', 'like', 'listen', 'live', 'look', 'love', 'make', 'mark',
            'mean', 'miss', 'move', 'need', 'note', 'open', 'own', 'pass', 'play', 'press', 'produce', 'pull', 
            'put', 'rain', 'run', 'reach', 'read', 'record', 'remember', 'rest', 'say',
            'seem', 'serve', 'set', 'show', 'sing', 'sit', 'sleep', 'spell', 'stand', 'start', 'stay',
            'stop', 'study', 'take', 'talk', 'teach', 'tell', 'think', 'travel', 'try', 'turn', 'use',
            'wait', 'walk', 'want', 'watch', 'wonder', 'work', 'write'
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