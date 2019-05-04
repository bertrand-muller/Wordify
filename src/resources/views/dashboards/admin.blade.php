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
                <section class="nes-container with-title">
                    <h3 class="title">Words to validate</h3>
                    @foreach($wordsToValidate as $wordToValidate)
                        <div class="row">
                            <div class="col-xs-7">
                                <button type="button" class="nes-btn smallPadding is-primary"><i class="fa fa-user"></i></button>
                                <button type="button" class="nes-btn smallPadding">?</button>
                                {{$wordToValidate->word}}
                            </div>
                            <div class="col-xs-5 text-right">
                                <button type="button" class="nes-btn smallPadding is-success">&#x2714;</button>
                                <button type="button" class="nes-btn smallPadding is-error">X</button>
                            </div>
                        </div>
                        <label class="split"></label>
                    @endforeach
                    @if(sizeof($wordsToValidate) == 0)
                        <p>
                            No word to validate
                        </p>
                    @endif
                </section>
            </section>
            <div class="col-md-7 profile">
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