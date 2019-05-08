@extends('layouts.app')

@section('title', 'Wordify')

@section('page')
<div class="container body">
    <div class="main_container">
        <div class="row title">
            <div class="col-md-4 col-md-offset-4">
                <section class="nes-container gameName">
                    Wordify
                </section>
            </div>

            @if(!$user->isGuest)
                <div class="col-md-4 logOut">
                    @if($user->id == 1)
                        <button id="profile_adminPannel_button" type="button" class="nes-btn is-warning">Admin panel</button>
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
                            <h2>What is Wordify ?</h2>
                            <p>
                                Wordify is a retro cooperative game where all players play together to discover a mystery word.
                                Find the best hint to help your teammate and be creative, because all similar hints will be removed !
                            </p>
                            <br/>
                            <br/>
                            <span><i class="nes-jp-logo"></i></span>
                        </div>
                        <div page="2">
                            <h2>Start a game</h2>
                            <p>
                                First player who joins the game will be the "host". He will be able to start the game whenever he wants.<br/>
                                If there is not enough player for the game, bots will replace missing players.
                            </p>
                            <h2>CGame timeline</h2>
                            <p>
                                A game is splitted in rounds.<br/>
                                Round :
                                <ul class="text-left">
                                    <li>A player who guesses the word is called the "guesser".</li>
                                    <li>Other players (named "helpers") will be able to see the mystery word and they will try to find the best hint.</li>
                                    <li>When all players have submited their words, they will remove similar ones.</li>
                                    <li>At the end, the host will try to guess the word.</li>
                                </ul>
                            </p>
                        </div>
                        <div page="3">
                            <h2>Choose a hint to help</h2>
                            <p>
                                Each helper has to send a hint related to the mystery word.
                            </p>
                            <h2>Select words</h2>
                            <p>
                                Helpers will now see all hints. If some hints are similar, hints may be removed.<br/>
                                To remove a word, press on the red cross under the given hint.
                                To validate it, press on the green check.
                                If you don't know if you have to choose it or not, click on the orange question mark.
                            </p>
                            <h2>Guess the word</h2>
                            <p>
                                Guesser will be able to see all selected hints and he will try to guess the word.<br/>
                                To guess a word, write it and send it. If you don't want to try, you can skip it by clicking on the skip button.
                            </p>
                        </div>
                        <div page="4">
                            <h2>End of a round</h2>
                            <p>
                                The round ends when the guesser has tried to guess or not the word.<br/>
                                The team scores :
                                <p class="nes-text is-success">-> 1 point if the guesser got the right word</p>
                                <p class="nes-text">-> 0 point if the guesser skipped his turn</p>
                                <p class="nes-text is-error">-> -1 point if the guesser put a wrong word</p>
                            </p>
                            <h2>End of a game</h2>
                            <p>
                                The game ends when all rounds are finished.<br/>
                                A game is won if the score is more than 0. Otherwise, players loose the game.
                            </p>
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
                    <br>
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
                    <br>
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
                        <section class="nes-container with-title helloSection">
                            <h3 class="title">Profile</h3>
                            <div class="text-center"><span class="nes-text">Hello {{ $user->name }} !</span></div>
                            {!! $profile !!}
                        </section>
                    </div>
                    <br>
                    <div class="row">
                        <section class="nes-container with-title updateProfile">
                            <h3 class="title">Update profile</h3>
                            {{ Form::open(['route' => 'updateProfile', 'files' => true]) }}
                                <div class="nes-field">
                                    <label>Name</label>
                                    <input type="text" name="name" class="nes-input" placeholder="Name" value="{{ $user->name  }}">
                                </div>
                                <div class="split"></div>
                                <br>
                                <div class="nes-field">
                                    <label>New password</label>
                                    <input type="password" name="password" class="nes-input" placeholder="Password" value="">
                                </div>
                                <div class="nes-field">
                                    <label>Confirm new password</label>
                                    <input type="password" name="password_confirmation" class="nes-input" placeholder="Password" value="">
                                </div>
                                <div class="split"></div>
                                <br>
                                <div class="nes-field">
                                    <label>Avatar</label>
                                    <div class="avatar-sectionInput">
                                        <input class="inputfile nes-input" id="picture-input" name="picture" type="file" accept=".jpeg,.png,.jpg,.JPG,.PNG">
                                        <label for="picture-input">
                                            <div class="nes-input nes-input-avatar">
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
                    <section class="nes-container with-title joinGameSection">
                        <h3 class="title">Join a game</h3>
                        <div class="nes-field" id="join_randomGame">
                            <button type="button" class="nes-btn is-primary randomGameButton">Random game</button>
                        </div>
                        <label>Join a game with an ID</label>
                        <div class="nes-field" id="join_gameWithId">
                            <input type="text" class="nes-input btnInInput" placeholder="Game ID" value="" maxlength="6">
                            <button class="nes-btn is-primary btnInInput">Join</button>
                        </div>
                    </section>
                </div>
                <br>
                <div class="row">
                    <section class="nes-container with-title" id="join_createGame">
                        <h3 class="title">Create a game</h3>
                        {{ Form::open(['route' => 'game.create']) }}
                            <div class="nes-field">
                                <label>Number of players</label>
                                <input type="number" name="nbPlayers" class="nes-input" placeholder="Players" value="5" min="3" max="7" />
                            </div>
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
                    <br>
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
                                <button type="button" class="nes-btn is-success">Submit word</button>
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