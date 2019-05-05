@extends('layouts.app')

@section('title', 'Gues the word')

@section('page')
<div class="container body">
    <div class="main_container">
        <div class="row title">
            <div class="col-md-4 col-md-offset-4">
                <section class="nes-container gameName">
                    Guess the word
                </section>
            </div>

            @if(!$user->isGuest)
                <div class="col-md-4 logOut">
                    @if($user->id == 1)
                        <button id="profile_adminPannel_button" type="button" class="nes-btn is-warning">Admin pannel</button>
                    @endif
                    <button id="profile_logOut_button" type="button" class="nes-btn is-error">Log out</button>
                </div>
            @endif
        </div>
        <div class="row">
            <div class="col-md-4 rules">
                <section class="nes-container with-title is-dark">
                    <h3 class="title">Rules</h3>
                    <div id="rules_content" max-page="4">
                        <div page="1">
                            TODO
                            <br/>PAGE 1 isdem diebus acciderat malum, quod et Theophilum insontem atrox interceperat casus, et Serenianus dignus exsecratione cunctorum, innoxius, modo non reclamante publico vigore, discessit.
                            <br/>
                            <br/>Utque aegrum corpus quassari etiam levibus solet offensis, ita animus eius angustus et tener, quicquid increpuisset, ad salutis suae dispendium existimans factum aut cogitatum, insontium caedibus fecit victoriam luctuosam.
                            <br/>
                            <br/>Hae duae provinciae bello quondam piratico catervis mixtae praedonum a Servilio pro consule missae sub iugum factae sunt vectigales. et hae quidem regiones velut in prominenti terrarum lingua positae ob orbe eoo monte Amano disparantur.
                        </div>
                        <div page="2">
                            PAGE 2 isdem diebus acciderat malum, quod et Theophilum insontem atrox interceperat casus, et Serenianus dignus exsecratione cunctorum, innoxius, modo non reclamante publico vigore, discessit.
                            <br/>
                            <br/>Utque aegrum corpus quassari etiam levibus solet offensis, ita animus eius angustus et tener, quicquid increpuisset, ad salutis suae dispendium existimans factum aut cogitatum, insontium caedibus fecit victoriam luctuosam.
                            <br/>
                            <br/>Hae duae provinciae bello quondam piratico catervis mixtae praedonum a Servilio pro consule missae sub iugum factae sunt vectigales. et hae quidem regiones velut in prominenti terrarum lingua positae ob orbe eoo monte Amano disparantur.
                        </div>
                        <div page="3">
                            PAGE 3 isdem diebus acciderat malum, quod et Theophilum insontem atrox interceperat casus, et Serenianus dignus exsecratione cunctorum, innoxius, modo non reclamante publico vigore, discessit.
                            <br/>
                            <br/>Utque aegrum corpus quassari etiam levibus solet offensis, ita animus eius angustus et tener, quicquid increpuisset, ad salutis suae dispendium existimans factum aut cogitatum, insontium caedibus fecit victoriam luctuosam.
                            <br/>
                            <br/>Hae duae provinciae bello quondam piratico catervis mixtae praedonum a Servilio pro consule missae sub iugum factae sunt vectigales. et hae quidem regiones velut in prominenti terrarum lingua positae ob orbe eoo monte Amano disparantur.
                        </div>
                        <div page="4">
                            PAGE 4 isdem diebus acciderat malum, quod et Theophilum insontem atrox interceperat casus, et Serenianus dignus exsecratione cunctorum, innoxius, modo non reclamante publico vigore, discessit.
                            <br/>
                            <br/>Utque aegrum corpus quassari etiam levibus solet offensis, ita animus eius angustus et tener, quicquid increpuisset, ad salutis suae dispendium existimans factum aut cogitatum, insontium caedibus fecit victoriam luctuosam.
                            <br/>
                            <br/>Hae duae provinciae bello quondam piratico catervis mixtae praedonum a Servilio pro consule missae sub iugum factae sunt vectigales. et hae quidem regiones velut in prominenti terrarum lingua positae ob orbe eoo monte Amano disparantur.
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-offset-1 col-xs-5">
                            <button type="button" class="nes-btn" id="rules_previous">Previous</button>
                        </div>
                        <div class="col-xs-5">
                            <button type="button" class="nes-btn" id="rules_next">Next</button>
                        </div>
                    </div>
                    <div class="text-center" id="rules_currentPage"></div>
                </section>
            </div>
            <div class="col-md-4 profile">
                @if (!$errors->isEmpty())
                    <div class="row">
                        <section class="nes-container with-title guestLogin">
                            <h3 class="title">Error</h3>
                            <span class="nes-text is-error">
                                @if($errors->first() == 'These credentials do not match our records.')
                                    Incorrect email or password
                                @else
                                    {!! $errors->first() !!}
                                @endif
                                </span>
                        </section>
                    </div>
                @endif
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
                            <div class="text-center"><span class="nes-text">Hello {{ $user->name }} !</span></div>
                            {!! $profile !!}
                        </section>
                    </div>
                    <div class="row">
                        <section class="nes-container with-title updateProfile">
                            <h3 class="title">Update profile</h3>
                            {{ Form::open(['route' => 'updateProfile', 'files' => true]) }}
                                <div class="nes-field">
                                    <label>Name</label>
                                    <input type="text" name="name" class="nes-input" placeholder="Name" value="{{ $user->name  }}">
                                </div>
                                <div class="split"></div>
                                <div class="nes-field">
                                    <label>New password</label>
                                    <input type="password" name="password" class="nes-input" placeholder="Password" value="">
                                </div>
                                <div class="nes-field">
                                    <label>Confirm new password</label>
                                    <input type="password" name="password_confirmation" class="nes-input" placeholder="Password" value="">
                                </div>
                                <div class="split"></div>
                                <div class="nes-field">
                                    <label>Avatar</label>
                                    <div class="avatar-sectionInput">
                                        <input class="inputfile nes-input" id="picture-input" name="picture" type="file" accept=".jpeg,.png,.jpg,.JPG,.PNG">
                                        <label for="picture-input">
                                            <div class="nes-input">
                                                <span class="nes-text" id="picture-output">Choose a file...</span>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="avatar-sectionDisplay">
                                        <div class="nes-input">
                                            <img class="nes-avatar" src="/uploads/users/{{$user->image}}"/>

                                        </div>
                                    </div>
                                </div>
                                <div class="nes-field">
                                    <button type="button" class="nes-btn">Update profile</button>
                                </div>
                            {{ Form::close() }}
                        </section>
                    </div>
                @endif
            </div>
            <div class="col-md-4 join">
                <div class="row">
                    <section class="nes-container with-title">
                        <h3 class="title">Join a game</h3>
                        <div class="nes-field" id="join_randomGame">
                            <button type="button" class="nes-btn is-primary">Random game</button>
                        </div>
                        <label>Join a game with ID</label>
                        <div class="nes-field" id="join_gameWithId">
                            <input type="text" class="nes-input btnInInput" placeholder="Game ID" value="" maxlength="6">
                            <button class="nes-btn is-primary btnInInput">Join</button>
                        </div>
                    </section>
                </div>
                <div class="row">
                    <section class="nes-container with-title" id="join_createGame">
                        <h3 class="title">Create a game</h3>
                        {{ Form::open(['route' => 'game.create']) }}
                            <div class="nes-field">
                                <label>Number of rounds</label>
                                <input type="number" name="nbRounds" class="nes-input" placeholder="Rounds" value="5" min="1" max="10" />
                            </div>
                            <label>Private game</label>
                            <div class="radioGroup">
                                <label>
                                    <input type="radio" class="nes-radio" name="isPrivate" value="no" checked/>
                                    <span>No</span>
                                </label>
                                <label>
                                    <input type="radio" class="nes-radio" name="isPrivate" value="yes" />
                                    <span>Yes</span>
                                </label>
                            </div>
                            <label></label>
                            <div class="nes-field">
                                <button type="button" class="nes-btn is-primary">Create game</button>
                            </div>
                        {{ Form::close() }}
                    </section>
                </div>
                @if(!$user->isGuest)
                    <div class="row">
                        <section class="nes-container with-title" id="join_submitWord">
                            <h3 class="title">Submit a new word</h3>
                            <div>
                                <span class="nes-text"></span>
                            </div>
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
                @endif
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