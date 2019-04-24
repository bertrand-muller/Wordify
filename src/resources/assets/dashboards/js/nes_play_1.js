

let e = new Echo({
    broadcaster: 'socket.io',
    host: window.location.hostname + ':6001'
})

e.channel('chan-demo')
.listen('PostCreatedEvent', function (e) {
    console.log('PostCreatedEvent', e)
})