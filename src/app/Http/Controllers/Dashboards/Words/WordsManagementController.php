<?php

namespace App\Http\Controllers\Dashboards\Words;

use App\Http\Controllers\Controller;
use App\Models\Words\Word;
use \Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Intervention\Image\Facades\Image;

class WordsManagementController extends Controller {

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }


    /**
     * Show the words management dashboard.
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('dashboards.words.words_management');
    }


    /**
     * Check if a french word already exists in database
     * @param $french
     * @return bool
     */
    private function checkFrenchWordAlreadyExists($french) {
        return (Word::where([
            ['french', '=', $french]
        ])->count() > 0);
    }


    /**
     * Check if an english word already exists in database
     * @param $french
     * @return bool
     */
    private function checkEnglishWordAlreadyExists($english) {
        return (Word::where([
            ['english', '=', $english]
        ])->count() > 0);
    }


    /**
     * Check if a word is valid before adding it to the database
     * @param $french Word in french
     * @param $english Word in english
     * @param $frenchDefinition Word definition in french
     * @param $englishDefinition Word definition in english
     * @return Response HTTP response
     */
    private function checkWordValidity($french, $english, $frenchDefinition, $englishDefinition) {

        // Is the french word empty ?
        if($french == '') { return Response::create(['error' => 'The french word is missing.'], 400); }

        // Is the english word empty ?
        if($english == '') { return Response::create(['error' => 'The english word is missing.'], 400); }

        // Is the french definition empty ?
        if($frenchDefinition == '') { return Response::create(['error' => 'The french definition is missing.'], 400); }

        // Is the english definition empty ?
        if($englishDefinition == '') { return Response::create(['error' => 'The english definition is missing.'], 400); }

        // Is the french word already defined ?
        if($this->checkFrenchWordAlreadyExists($french)) { return Response::create(['error' => 'This french word already exists.'], 400); }

        // Is the english word already defined ?
        if($this->checkEnglishWordAlreadyExists($english)) { return Response::create(['error' => 'This english word already exists.'], 400); }

        return Response::create(['message' => 'Valid'], 200);

    }


    /**
     * Add a word according to parameters specified by the user
     * @param Request $request
     * @return false|Response|string
     */
    public function addWord(Request $request) {

        // Get data by sanitizing them
        $french = filter_var($request->input('french'), FILTER_SANITIZE_STRING);
        $english = filter_var($request->input('english'), FILTER_SANITIZE_STRING);
        $frenchDefinition = filter_var($request->input('frenchDefinition'), FILTER_SANITIZE_STRING);
        $englishDefinition = filter_var($request->input('englishDefinition'), FILTER_SANITIZE_STRING);


        // Do all verifications and add the word.
        $response = $this->checkWordValidity($french, $english, $englishDefinition, $frenchDefinition);
        if($response->status() == 200) {
            try {

                $word = new Word();
                $word->french = $french;
                $word->english = $english;
                $word->frenchDefinition = $frenchDefinition;
                $word->englishDefinition = $englishDefinition;
                $word->user_id = auth()->user()->id;

                // Check if request contains a picture
                if ($request->hasFile('picture')) {

                    $picture = $request->file('picture');
                    $extension = $picture->getClientOriginalExtension();

                    // Verify extension.
                    if (!in_array($extension, ['jpeg', 'jpg', 'JPG', 'PNG', 'png'])) {
                        return Response::create(['error' => 'Only the following formats are accepted to upload a picture: ".jpeg", ".jpg" ou ".png"'], 400);
                    }

                    // Verify size is less than 4Mo (4194304 octets).
                    if ($picture->getClientSize() > 4194304) {
                        return Response::create(['error' => 'The picture is too big. It should be less than 4Mo.'], 400);
                    }

                    $filename = uniqid() . time() . '.' . $extension;
                    Image::make($picture)->resize(800, 800)->save(public_path('/uploads/words/' . $filename));

                    $word->picture = $filename;

                }

                $word->save();

                return json_encode([
                    'french' => $word->french,
                    'english' => $word->english,
                    'frenchDefinition' => $word->frenchDefinition,
                    'englishDefinition' => $word->englishDefinition
                ]);

            } catch (Exception $e) {
                return Response::create(['error' => 'An error occured while saving your word. Please try again.' . $e], 400);
            }

        }

        // Return error.
        return $response;

    }

}