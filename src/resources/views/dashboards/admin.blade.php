@extends('layouts.app')

@section('title', 'Guess the word')

@section('page')
<div class="container body">
    <!-- API TO USE: https://www.wordsapi.com/ -->
    <div class="main_container">
        <!-- Modal -->
        <div class="modal fade" id="exitModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content is-dark">
                    <div class="modal-body nes-dialog is-dark">
                        <p class="title">Exit game</p>
                        <p>Are you sure to want to exit this game ?</p>
                        <menu class="dialog-menu">
                            <button type="button" class="nes-btn" data-dismiss="modal">Cancel</button>
                            <button type="button" class="nes-btn is-error" id="btn-exitGame">Exit</button>
                        </menu>
                    </div>
                </div>
            </div>
        </div>
        <div class="row title">
            <div class="col-md-4 col-md-offset-4">
                <section class="nes-container gameName">
                    Guess the word
                </section>
            </div>
            @if(!$user->isGuest)
                <div class="col-md-4 logOut">
                    <button id="profile_index_button" type="button" class="nes-btn is-warning">Index</button>
                    <button id="profile_logOut_button" type="button" class="nes-btn is-error">Log out</button>
                </div>
            @endif
        </div>
        <div class="row">
            <div class="col-md-5 wordsToValidate">
                <section class="nes-container with-title" id="wordValidated">
                    <h3 class="title">Words to validate</h3>
                    @foreach($wordsToValidate as $wordToValidate)
                        <div class="row" wordId="{{$wordToValidate->id}}">
                            <div class="col-xs-3">
                                <button type="button" class="nes-btn smallPadding smallMargin is-primary btn-profile" userId="{{$wordToValidate->userId}}"><i class="fa fa-user"></i></button>
                                <button type="button" class="nes-btn smallPadding smallMargin btn-question">?</button>
                            </div>
                            <div class="wordHeight col-xs-5">
                                <span>{{$wordToValidate->word}}</span>
                            </div>
                            <div class="col-xs-4 text-right">
                                <button type="button" class="nes-btn smallPadding smallMargin is-success btn-check">&#x2714;</button>
                                <button type="button" class="nes-btn smallPadding smallMargin is-error btn-cross">X</button>
                            </div>
                        </div>
                        <label class="split"></label>
                    @endforeach
                    <p>
                        No word to validate
                    </p>
                </section>
            </div>
            <div class="col-md-3 manageWords">
                <section class="nes-container with-title" id="findWord">
                    <h3 class="title">Find a word</h3>
                    <div class="nes-field">
                        <label>Word</label>
                        <input type="text" class="nes-input" placeholder="Word to find" maxlength="20">
                    </div>
                    <label></label>
                    <div class="nes-field">
                        <button type="button" class="nes-btn is-primary">Find the word</button>
                    </div>
                    <label></label>
                    <div class="nes-field">
                        <button type="button" class="nes-btn is-warning">Remove filter</button>
                    </div>
                </section>
                <section class="nes-container with-title" id="addWord">
                    <h3 class="title">Add a word</h3>
                    <div><span class="nes-text"></span></div>
                    <div class="nes-field">
                        <label>Word</label>
                        <input type="text" class="nes-input" placeholder="Word to add" maxlength="20">
                    </div>
                    <label></label>
                    <div class="nes-field">
                        <button type="button" class="nes-btn is-success">Add the word</button>
                    </div>
                </section>
            </div>
            <div class="col-md-4 wordsToValidate">
                <section class="nes-container with-title" id="word-list">
                    <h3 class="title">Words validated words</h3>
                    @foreach($words as $word)
                        <div class="row" wordId="{{$word->id}}">
                            <div class="col-xs-2">
                                <button type="button" class="nes-btn smallPadding smallMargin btn-question">?</button>
                            </div>
                            <div class="wordHeight col-xs-8">
                                <span>{{$word->word}}</span>
                            </div>
                            <div class="col-xs-2 text-right">
                                <button type="button" class="nes-btn smallPadding smallMargin is-error btn-cross">X</button>
                            </div>
                        </div>
                        <label class="split"></label>
                    @endforeach
                    <p>
                        No word in database
                    </p>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

@javascript([

])

@section('scripts')
    @parent
    {{ Html::script(mix('assets/app/js/websocket.js')) }}
    {{ Html::script(mix('assets/dashboards/js/modal.js')) }}
    {{ Html::script(mix('assets/dashboards/js/admin.js')) }}
@endsection

@section('styles')
    @parent
    {{ Html::style(mix('assets/css/nes.css')) }}
@endsection