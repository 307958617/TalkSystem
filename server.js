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
        let loginTime = (new Date()).getTime();
        let newUser = JSON.parse(data);
        newUser['loginTime'] = loginTime;
        newUser['name'] = toUnicode(newUser['name']);
        socket.user = JSON.stringify(newUser);
        socket.broadcast.emit('sockNewUser',newUser);
        users[socket.user] = socket.id;
        upDateUsers();
    });

    function toUnicode(s){//只将汉字转换成Unicode，英文不转换
        return s.replace(/([\u4E00-\u9FA5]|[\uFE30-\uFFA0])/g,function(newStr){
            return "\\u" + newStr.charCodeAt(0).toString(16);
        });
    }

    function upDateUsers() {
        io.emit('sockUsers',Object.keys(users))
    }

    socket.on('disconnect',function (data) {
        if(!socket.user) return;
        delete users[socket.user];
        upDateUsers()
    })

});