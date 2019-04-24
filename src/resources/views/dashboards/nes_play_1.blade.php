@extends('layouts.app')

@section('title', 'Play 1')

@section('page')
<div class="container body">
    <div class="main_container">
        <div class="row">
            <div class="col-md-8">
            TEST
            </div>
            <div class="col-md-4">
                <section class="nes-container with-title smallPadding chat">
                    <h3 class="title">Chat</h3>
                    <div id="chat" class="item">
                        <section class="message-list smallMargin" id="chat-messages"></section>
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
    'currentUserId' => $currentUserId,
])

@section('scripts')
    @parent
    {{ Html::script(mix('assets/app/js/websocket.js')) }}
    {{ Html::script(mix('assets/dashboards/js/game.js')) }}
@endsection

@section('styles')
    @parent
    {{ Html::style(mix('assets/css/nes.css')) }}
@endsection