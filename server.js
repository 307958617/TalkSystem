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
    redisClient.subscribe('chatroom');//监听频道为chatroom的事件
    redisClient.subscribe('user_image_upload');//监听频道为user_image_upload的事件

    redisClient.on('message',function (channel,message) {//这会根据频道的不同，自动变换频道和需要传递的数据
        // console.log('ss'+ channel + message);
        socket.emit(channel,message);//发布消息，这里的频道可以是chatroom或user_image_upload
    });

    socket.on('typing',function (data) {//监听typing事件，该事件是表示用户正在输入字符
        // console.log(data + 'is typing');
        socket.broadcast.emit('sockTyping',data)//将该事件发布出去，但是自己除外。
    });

    socket.on('stopTyping',function () {//监听stopTyping事件，该事件是表示用户已停止输入字符
        socket.broadcast.emit('sockStopTyping')//将该事件发布出去，但是自己除外。
    });
    // 用户进入提示
    socket.on('new user',function (data) {//监听new user事件，该事件表示用户进入
        let loginTime = (new Date()).getTime();//记录进入的时间
        let newUser = JSON.parse(data);//json化数据并赋值给newUser
        newUser['loginTime'] = loginTime;//将进入时间添加到newUser里面
        newUser['name'] = toUnicode(newUser['name']);//对newUser里面的'name'字段即汉字转换成Unicode
        socket.user = JSON.stringify(newUser);//将newUser转换才字符串，存储到socket的user字段里面
        socket.broadcast.emit('sockNewUser',newUser);//将newUser数据广播出去，表示有人进入了聊天室
        users[socket.user] = socket.id;//将新加入的user保存到users里面，每个user对应的相应的socket.id值
        upDateUsers();//将当前登录的所有用户信息广播出去给所有人，包括自己
    });

    function toUnicode(s){//只将汉字转换成Unicode，英文不转换
        return s.replace(/([\u4E00-\u9FA5]|[\uFE30-\uFFA0])/g,function(newStr){
            return "\\u" + newStr.charCodeAt(0).toString(16);
        });
    }

    function upDateUsers() {//将当前登录的所有用户信息广播出去给所有人，包括自己
        io.emit('sockUsers',Object.keys(users))//广播的数据只是键，即只是socket.user(也可以说是newUser)信息
    }

    socket.on('disconnect',function (data) {
        if(!socket.user) return;
        delete users[socket.user];//如果用户退出登录，那么就将users里面删除当前的登录用户
        upDateUsers()
    })

});