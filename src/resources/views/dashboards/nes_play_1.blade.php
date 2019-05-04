@extends('layouts.app')

@section('title', 'Play 1')

@section('page')
<div class="container body">
    <!-- API TO USE: https://www.wordsapi.com/ -->
    <!--
    DEBUG :
    REDO :
    ADMIN:
        - add word
        - remove word
        - validate word
     BADGES:
        - débutant ... experts
        - pourcentage réussite global
        - pourcentage réussite deviner
        - pourcentage réussite faire deviner
        - parties jouées
        - display profile
     HELP:
        - get word definition
     BOTS:
        - get synonym / submit word = API
        - select words
        - choose word / pass => API + union
        - choose bot quantity + bots replace players which leaved if players < 5
    -->
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
        <div class="modal fade" id="profilModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body nes-dialog">
                        <p class="title">Your profile</p>
                        <p>TODO</p>
                        <menu class="dialog-menu">
                            <button type="button" class="nes-btn" data-dismiss="modal">Ok</button>
                        </menu>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="definitionModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body nes-dialog">
                        <p class="title">Definition : <span></span></p>
                        <p class="content"></p>
                        <menu class="dialog-menu">
                            <button type="button" class="nes-btn" data-dismiss="modal">Ok</button>
                        </menu>
                    </div>
                </div>
            </div>
        </div>
        <div class="menu">
            <div class="row">
                <div class="col-md-3 text-center">
                    <button type="button" class="nes-btn is-error exit" data-toggle="modal" data-target="#exitModal">Exit</button>
                    <button type="button" class="nes-btn player" data-toggle="modal" data-target="#profilModal">{{ $currentUserName }}</button>
                </div>
                <div class="col-md-5">
                    <section class="nes-container noMargin gameName">
                        Guess the word
                    </section>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 onlineUsers">
                <section class="nes-container with-title is-dark">
                    <h3 class="title">Game ID</h3>
                    <div class="item" id="players-gameInfo">
                        {{$gameKey}}
                    </div>
                </section>
                <section class="nes-container with-title is-dark">
                    <h3 class="title">Guesser</h3>
                    <div class="item" id="players-chooser">
                        >_
                    </div>
                </section>
                <section class="nes-container with-title is-dark" id="players-game">
                    <h3 class="title">Game</h3>
                    <div class="score">
                        Score : <span class="nes-text">0</span>
                    </div>
                    <label></label>
                    @for($i = 1; $i <= json_decode($game)->nbRounds; $i++)
                        <div class="round" roundId="{{$i}}">
                            Round {{$i}} : <span>-</span>
                        </div>
                    @endfor
                </section>
                <section class="nes-container with-title is-dark">
                    <h3 class="title">Players</h3>
                    <div class="item" id="players-list">
                    </div>
                </section>
            </div>
            <div class="col-md-5 gamePannel">
                <div class="row" id="informations">
                    <section class="nes-container with-title informations">
                        <h3 class="title">Informations</h3>
                        <span class="nes-text" id="noInformations">No informations to display</span>
                    </section>
                </div>
                <div class="row" id="game">
                    <section class="nes-container" id="game-begin">
                            <p>Waiting for players<span class="dot"></span></p>
                            <progress class="nes-progress is-pattern" value="1" max="7"></progress>
                            <button type="button" class="nes-btn is-disabled play">1/7 players</button>
                    </section>
                    <section class="nes-container with-title" id="game-timer">
                        <h3 class="title">Remaining time</h3>
                        <progress class="nes-progress" value="100" max="100"></progress>
                    </section>
                    <section class="nes-container with-title" id="game-word_status">
                        <h3 class="title">Status</h3>
                        <span class="nes-text"></span>
                    </section>
                    <section class="nes-container with-title" id="game-wordGuesser_display">
                        <h3 class="title">Word of the guesser</h3>
                        <span class="nes-text"></span>
                    </section>
                    <section class="nes-container with-title" id="game-word_display">
                        <h3 class="title">Word to be guessed</h3>
                        <span class="nes-text"></span>
                    </section>
                    <section class="nes-container with-title" id="game-word_chooserInput">
                        <h3 class="title">Word to guess</h3>
                        <div class="nes-field">
                            <input type="text" class="nes-input" placeholder="Type the good word" />
                            <button type="button" class="nes-btn">Guess the word</button>
                        </div>
                        <span class="nes-text">If you don't know the word, you can pass</span>
                        <button type="button" class="nes-btn is-error">Don't guess the word</button>
                    </section>
                    <section class="nes-container with-title" id="game-word_helperInput">
                        <h3 class="title">Word to help</h3>
                        <div class="nes-field">
                            <input type="text" class="nes-input" placeholder="Type a word to help the guesser" />
                            <button type="button" class="nes-btn">Send the word</button>
                        </div>
                    </section>
                    <section class="nes-container with-title" id="game-word_current">
                        <h3 class="title">Word of other players</h3>
                        <div id="game-word_current_words">
                        </div>
                    </section>
                    <section class="nes-container with-title" id="game-allRounds">
                        <h3 class="title">All rounds</h3>
                        @for($i = 1; $i <= json_decode($game)->nbRounds; $i++)
                            <section class="nes-container with-title">
                                <h3 class="title">Round {{$i}}</h3>
                                <span round="{{$i}}"></span>
                            </section>
                        @endfor
                    </section>
                </div>
            </div>
            <div class="col-md-4">
                <section class="nes-container with-title smallPadding chat">
                    <h3 class="title">Chat</h3>
                    <div id="chat" class="item">
                        <section class="message-list smallMargin" id="chat-messages">
                            <section class="message smallMargin -left" msg-userId="0">
                                <i class="smallMargin">
                                    <img class="nes-avatar is-rounded pixelated" src="/images/root_nb.png">
                                    <br>Root
                                </i>
                                <div class="nes-balloon smallPadding from-left">
                                    <p>Welcome on this game !<br/>
                                        You can chat with other players by typping a text below !<br/>
                                        Have fun !
                                    </p>
                                </div>
                            </section>
                        </section>
                        <section class="message-list smallMargin">
                            <section class="message smallMargin" id="chat-input-section">
                                <input type="text" id="chat-input" class="nes-input btnInInput" placeholder="Type your message here...">
                                <a class="nes-btn largeText btnInInput" id="chat-btn">&gt;</a>
                            </section>
                        </section>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

@javascript([
    'gameId' => $gameId,
    'game' => $game,
    'words' => $words,
    'currentWord' => $currentWord,
    'currentUserId' => $currentUserId,
    'currentUserName' => $currentUserName,
])

@section('scripts')
    @parent
    {{ Html::script(mix('assets/app/js/websocket.js')) }}
    {{ Html::script(mix('assets/dashboards/js/game.js')) }}
    {{ Html::script(mix('assets/dashboards/js/modal.js')) }}
@endsection

@section('styles')
    @parent
    {{ Html::style(mix('assets/css/nes.css')) }}
@endsection