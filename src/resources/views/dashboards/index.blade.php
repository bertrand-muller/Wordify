@extends('layouts.app')

@section('title', 'Gues the word')

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
        </div>
        <div class="row">
            <div class="col-md-4 rules">
                <section class="nes-container with-title is-dark">
                    <h3 class="title">Rules</h3>
                    <div>
                        TODO
                        <br/>Duplexque isdem diebus acciderat malum, quod et Theophilum insontem atrox interceperat casus, et Serenianus dignus exsecratione cunctorum, innoxius, modo non reclamante publico vigore, discessit.
                        <br/>
                        <br/>Utque aegrum corpus quassari etiam levibus solet offensis, ita animus eius angustus et tener, quicquid increpuisset, ad salutis suae dispendium existimans factum aut cogitatum, insontium caedibus fecit victoriam luctuosam.
                        <br/>
                        <br/>Hae duae provinciae bello quondam piratico catervis mixtae praedonum a Servilio pro consule missae sub iugum factae sunt vectigales. et hae quidem regiones velut in prominenti terrarum lingua positae ob orbe eoo monte Amano disparantur.
                    </div>
                    <div class="row">
                        <div class="col-xs-offset-1 col-xs-5">
                            <button type="button" class="nes-btn">Previous</button>
                        </div>
                        <div class="col-xs-5">
                            <button type="button" class="nes-btn">Next</button>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-md-4 profile">
                @if($user->isGuest)
                    <div class="row">
                        <section class="nes-container with-title guestLogin">
                            <h3 class="title">Profile</h3>
                            <span class="nes-text is-disabled">You are logged as {{ $user->name }}</span>
                        </section>
                    </div>
                    <div class="row">
                        <section class="nes-container with-title loginForm">
                            <h3 class="title">Log in</h3>
                            {{ Form::open(['route' => 'login']) }}
                            <div class="nes-field">
                                <label>Email</label>
                                <input type="text" class="nes-input" name="email" placeholder="Email" autocomplete="email" value="">
                            </div>
                            <div class="nes-field">
                                <label>Password</label>
                                <input type="password" class="nes-input" name="password" placeholder="Password" value="">
                            </div>
                            <div class="nes-field">
                                <button type="button" class="nes-btn is-success">Log in</button>
                            </div>
                            {{ Form::close() }}
                        </section>
                    </div>
                    <div class="row">
                        <section class="nes-container with-title signupForm">
                            <h3 class="title">Sign up</h3>
                            {{ Form::open(['route' => 'register']) }}
                                <div class="nes-field">
                                    <label>Name</label>
                                    <input type="text" name="name" class="nes-input" placeholder="Name" value="">
                                </div>
                                <div class="nes-field">
                                    <label>Email</label>
                                    <input type="text" name="email" class="nes-input" placeholder="Email" value="">
                                </div>
                                <div class="nes-field">
                                    <label>Password</label>
                                    <input type="password" name="password" class="nes-input" placeholder="Password" value="">
                                </div>
                                <div class="nes-field">
                                    <label>Confirm password</label>
                                    <input type="password" name="password_confirmation" class="nes-input" placeholder="Password" value="">
                                </div>
                                <div class="nes-field">
                                    <button type="button" class="nes-btn is-primary">Sign up</button>
                                </div>
                            {{ Form::close() }}
                        </section>
                    </div>
                @else
                    <div class="row">
                        <section class="nes-container with-title">
                            <h3 class="title">Profile</h3>
                            <span class="nes-text">Hello {{ $user->name }} !</span>
                        </section>
                    </div>
                    <div class="row">
                        <section class="nes-container with-title">
                            <h3 class="title">Log out</h3>
                            <button id="profile_logOut_button" type="button" class="nes-btn is-error">Log out</button>
                        </section>
                    </div>
                @endif
            </div>
            <div class="col-md-4 join">
                <div class="row">
                    <section class="nes-container with-title">
                        <h3 class="title">Join a game</h3>
                        <div class="nes-field">
                            <button type="button" class="nes-btn is-primary">Random game</button>
                        </div>
                        <label>Join a game with ID</label>
                        <div class="nes-field">
                            <input type="text" class="nes-input btnInInput" placeholder="Game ID" value="" maxlength="6">
                            <a class="nes-btn is-primary btnInInput">Join</a>
                        </div>
                    </section>
                </div>
                <div class="row">
                    <section class="nes-container with-title">
                        <h3 class="title">Create a game</h3>
                        <div class="nes-field">
                            <label>Number of rounds</label>
                            <input type="number" class="nes-input" placeholder="Rounds" value="5" min="1" max="10">
                        </div>
                        <label>Private game</label>
                        <div class="radioGroup">
                            <label>
                                <input type="radio" class="nes-radio" name="answer" checked/>
                                <span>No</span>
                            </label>
                            <label>
                                <input type="radio" class="nes-radio" name="answer" />
                                <span>Yes</span>
                            </label>
                        </div>
                        <label></label>
                        <div class="nes-field">
                            <button type="button" class="nes-btn is-primary">Create game</button>
                        </div>
                    </section>
                </div>
                <div class="row">
                    <section class="nes-container with-title">
                        <h3 class="title">Submit a new word</h3>
                        <div class="nes-field">
                            <label>Word</label>
                            <input type="text" class="nes-input" placeholder="Word to submit" maxlength="20">
                        </div>
                        <label></label>
                        <div class="nes-field">
                            <button type="button" class="nes-btn is-success">Sumbit word</button>
                        </div>
                    </section>
                </div>
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
    {{ Html::script(mix('assets/dashboards/js/index.js')) }}
@endsection

@section('styles')
    @parent
    {{ Html::style(mix('assets/css/nes.css')) }}
@endsection