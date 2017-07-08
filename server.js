var app = require('express')();
var server = require('http').Server(app);
var io = require('socket.io')(server);
var redis = require('redis');
let users = {};

server.listen(3000,function () {
    console.log('server start')
});

io.on('connection',function (socket) {
    console.log('a new connected');
    var redisClient = redis.createClient();
    redisClient.subscribe('chatroom');
    redisClient.on('message',function (channel,message) {
        // console.log('ss'+ channel + message);
        socket.emit(channel,message);
    });

    socket.on('typing',function (data) {
        // console.log(data + 'is typing');
        socket.broadcast.emit('sockTyping',data)
    });

    socket.on('stopTyping',function () {
        socket.broadcast.emit('sockStopTyping')
    });
    // 用户进入提示
    socket.on('new user',function (data) {
        // console.log(data);
        socket.user = data;
        console.log(data);
        socket.broadcast.emit('sockNewUser',data);
        users[JSON.stringify(socket.user)] = socket.id;
        upDateUsers();
    });

    function upDateUsers() {
        io.emit('sockUsers',Object.keys(users))
    }

    socket.on('disconnect',function (data) {
        if(!socket.user) return;
        delete users[JSON.stringify(socket.user)];
        upDateUsers()
    })

});