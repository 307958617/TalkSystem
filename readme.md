<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

# 实现聊天室功能+头像图片上传后无刷新显示
## 一、前期准备：
### 1、引入AdminLTE，以便使用它的一些css样式。
#### ①、执行php artisan make:auth
#### ②、只需要将adminLTE_css_js引入后代码文件夹里面的内容替换现有的内容。
#### ③、执行npm install 命令安装依赖之后，执行npm run dev进行编译后即可调用adminLTE的样式了。

### 2、重新设置users表字段，添加gender(性别)、avatar(头像)、position(头衔)、about(简介)：
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('gender');
            $table->string('avatar');
            $table->string('email')->unique();
            $table->string('position')->nullable();
            $table->text('about')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }
### 3、增加messages表用来放置聊天的相关内容更：
#### ①、执行php artisan make:model Message -m;
#### ②、messages表内容为：
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->text('message');
            $table->timestamps();
        });
    }
### 4、设置Message Model与User Model的关联关系：
#### ①、Message Model中内容
    class Message extends Model
    {
        protected $fillable=['message','user_id'];
        public function user()
        {
            return $this->belongsTo('App\User');
        }
    }
#### ②、User Model中内容
    class User extends Authenticatable
    {
        use Notifiable;
    
        protected $fillable = [
            'name', 'email', 'password','avatar','gender','about','position'
        ];
    
        protected $hidden = [
            'password', 'remember_token',
        ];
    
        public function messages()
        {
            return $this->hasMany('App\Message');
        }
    }
### 5、执行php artisan migrate进行数据库迁移，将表写入数据库。
    在执行上面的命令前需要修改.env 配置文件关于数据库方面的设置：
        DB_DATABASE=talk
        DB_USERNAME=homestead
        DB_PASSWORD=secret
    然后还要到数据库新建一个名为talk的数据库。
## 二、修改用户注册界面，添加一个性别选项，并根据性别自动匹配默认的头像：
### 1、到resources->views->auth->register.blade.php里面添加如下代码：
    <div class="form-group{{ $errors->has('gender') ? ' has-error' : '' }}">
        <label for="gender" class="col-md-4 control-label">Gender</label>

        <div class="col-md-6">
            <select id="gender" type="text" class="form-control" name="gender">
                <option value="M">Male</option>
                <option value="F">Female</option>
            </select>
            @if ($errors->has('gender'))
                <span class="help-block">
                <strong>{{ $errors->first('gender') }}</strong>
            </span>
            @endif
        </div>
    </div>
### 2、修改app->Http->Controllers->Auth->RegisterController.php:
    protected function create(array $data)
    {
        if($data['gender'] == 'M'):
            $avatar = asset('storage/images/avatars/male.jpg');
        elseif($data['gender'] == 'F'):
            $avatar = asset('storage/images/avatars/female.jpg');
        endif;
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'gender' => $data['gender'],
            'avatar' => $avatar,
        ]);

        return $user;
    }
### 3、到storage->app->public->images->avatars目录下面创建头像文件male.jpg和female.jpg：
### 4、到这里应该还不会显示头像，在public里面关联storage才可显示头像,需要执行如下命令：
    php artisan storage:link
## 三、实现类QQ实时聊天功能：
### 1、在web.php路由文件分配一个用于显示聊天界面的路由：
    Route::get('/chat','ChatController@showChat')->name('chat.show');
### 2、新建ChatController，执行php artisan make:controller ChatController ;具体内容为：
    public function showChat()
    {
        return view('chat');
    }
### 3、在views里面创建chat.blade.php文件用于显示聊天界面：
    @extends('layouts.app')
 
    @section('content')
    <div class="container">
        <chat user="{{ Auth::user() }}"></chat><!-- 这是调用的vue组件 -->
    </div>
    @endsection
### 4、到resources->assets->js->components里面创建名为chat.vue的文件，这里面是具体界面和实现逻辑：
#### ①、chat.vue的具体内容为：
    <template>
        <div class="row">
            <div class="col-md-8">
                <div class="box box-warning direct-chat direct-chat-warning"><!--direct-chat-warning 可以控制聊天框的颜色-->
                    <div class="box-header with-border">
                        <h3 class="box-title">Direct Chat</h3><!--标题-->
    
                        <div class="box-tools pull-right">
                            <span data-toggle="tooltip" title="" class="badge bg-yellow" data-original-title="3 New Messages">{{ users.length }}</span><!-- 当前在线用户数 -->
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-box-tool" data-toggle="tooltip" title="" data-widget="chat-pane-toggle" data-original-title="Contacts">
                                <i class="fa fa-comments"></i></button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <!-- Conversations are loaded here -->
                        <div class="direct-chat-messages" id="chatContainer">
                            <!-- Message. Default to the left -->
                            <!-- 这里需要判断message是否是登陆用户发出的，如果不是登陆用户发出，就显示在左边，如果是，就显示在右边 -->
                            <div class="direct-chat-msg" :class="[Luser.id == message.user_id ? 'right' :'']" v-for="message in messages">
                                <div class="direct-chat-info clearfix">
                                    <span class="direct-chat-name pull-left">{{ message.user.name }}</span><!-- 发送该消息人名 -->
                                    <span class="direct-chat-timestamp pull-right">{{ message.created_at }}</span><!-- 该消息发送的时间 -->
                                </div>
                                <!-- /.direct-chat-info -->
                                <img class="direct-chat-img" :src="message.user.avatar" alt="message user image"><!-- 发送消息的人的头像 -->
                                <div class="direct-chat-text">
                                    {{ message.message }}<!-- 具体消息内容 -->
                          </div>
                                <!-- /.direct-chat-text -->
                            </div>
                            <!-- /.direct-chat-msg -->
                        </div>
                        <!--/.direct-chat-messages-->
                        <!-- Contacts are loaded here -->
                        <!-- 该用户有那些联系人即有那些好友，现在暂时不处理 -->
                        <div class="direct-chat-contacts">
                            <ul class="contacts-list">
                                <li>
                                    <a href="#">
                                        <img class="contacts-list-img" src="" alt="User Image">
    
                                        <div class="contacts-list-info">
                                    <span class="contacts-list-name">
                                      Count Dracula
                                      <small class="contacts-list-date pull-right">2/28/2015</small>
                                    </span>
                                            <span class="contacts-list-msg">How have you been? I was...</span>
                                        </div>
                                        <!-- /.contacts-list-info -->
                                    </a>
                                </li>
                                <!-- End Contact Item -->
                                <li>
                                    <a href="#">
                                        <img class="contacts-list-img" src="" alt="User Image">
    
                                        <div class="contacts-list-info">
                                    <span class="contacts-list-name">
                                      Sarah Doe
                                      <small class="contacts-list-date pull-right">2/23/2015</small>
                                    </span>
                                            <span class="contacts-list-msg">I will be waiting for...</span>
                                        </div>
                                        <!-- /.contacts-list-info -->
                                    </a>
                                </li>
                                <!-- End Contact Item -->
                            </ul>
                            <!-- /.contatcts-list -->
                        </div>
                        <!-- /.direct-chat-pane -->
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <form @submit.prevent="sendMessage">
                            <div class="input-group">
                                <input @input="typingWords" v-model="content" type="text" name="message" placeholder="Type Message ..." class="form-control">
                                <span class="input-group-btn">
                                <button type="submit" class="btn btn-warning btn-flat">Send</button>
                              </span>
                            </div>
                        </form>
                        <div id="typingBox" v-show="isTyping">{{ typingUserName }} 正在输入...</div><!-- 用来显示当前是谁正在打字-->
                    </div>
                    <!-- /.box-footer-->
                </div>
            </div>
            <div class="col-md-4">
                <members :users="users"></members><!-- 引入vue组件，用于显示当前所有登陆用户的头像信息-->
            </div>
        </div>
    </template>
    
    <script>
        export default {
            mounted() {
                $('.contacts-list').slimScroll(); //给联系人列表框添加滚动条样式
                this.getMessages();//获取当前所有的聊天信息
            },
            watch: {//根据监测聊天数据的变化，实现每次发送信息后，滚动条总是在最下面
                messages() {
    //                console.log("messages change");
                    this.$nextTick(() => {
                        let container = this.$el.querySelector("#chatContainer");
    //                    console.log(container);
                        container.scrollTop = container.scrollHeight;
                        $('#chatContainer').slimScroll({
                            start: 'bottom'
                        });
                    });
    //                document.getElementById('chatContainer').scrollTop = document.getElementById('chatContainer').scrollHeight+150;
                }
            },
            props:['user'],//获取从chat.blade.php界面传递进来的当前登陆用户的信息
            data() {//所有需要初始化的变量和常量
                return {
                    messages:[],//所有的聊天信息
                    message:{},//单个聊天信息
                    content:'',//具体的聊天内容，即input输入的内容
                    Luser:JSON.parse(this.user),//将通过props传递过来的用户数据格式化为json数据赋值给Luser
                    isTyping:false,//设置用户当前的输入状态，是否在输入，默认是没有输入
                    typingUserName:'',//表示当前正在输入的用户名称
                    lastTypingTime:'',//表示上一次输入的时间，也可看成开始输入的时间
                    typingTimer:'',//表示当前输入的时间，也可看成最后一次输入的时间
                    timeDiff:'',//表示开始到结束的时间差
                    TYPING_TIMER_LENGTH:500,//表示正在输入的状态在停止输入后持续多长时间才结束
                    users:{}, //表示当前所有登陆用户的信息
                }
            },
            sockets:{
                connect() {
    //                console.log(this.Luser.name + '进入了房间');
                    //当用户登陆之后，就给后台发送一条消息频道为:'new user'，并将当前用户信息(this.user)传递过去
                    this.$socket.emit('new user',this.user)
                },
                sockNewUser(data) {//监听经过后台处理之后的新增加用户事件sockNewUser。
                    console.log(data.name + '加入了房间')
                },
                sockUsers(data) {//监听经过后台处理之后的当前所有登陆的用户信息sockUsers。
                    this.users = data;
                    console.log(data);
                },
                chatroom(data) {//监听后台的chatroom事件，即用户提交并保持消息后的事件
                    let msg = JSON.parse(data);//获取json格式数据
    //                console.log(msg.data);
                    let message = msg.data.message;
                    let user = msg.data.user;
                    //设置消息的格式，即设置消息由那些要素组成：如：创建事件，消息内容，消息的发送人id，消息发送人的具体信息
                    this.message = {created_at:message.created_at,message:message.message,user_id:message.user_id,user:user};
                    //将每条消息添加到消息列表里面。
                    this.messages.push(this.message);
                },
                sockTyping(data) {//监听用户正在输入的状态，接收传递过来的用户信息
                    this.typingUserName = data;
                    this.isTyping = true;//将状态设置为正在输入
                },
                sockStopTyping() {//监听用户停止输入事件
                    this.isTyping = false;//如果用户停止输入超过500ms，那么就算停止。
                }
            },
            methods:{
                typingWords() {//用户输入时触发的输入事件，以及停止输入的触发事件在一起。
                    this.$socket.emit('typing',this.Luser.name);//当用户在输入时，发送一条消息给后台，频道为'typing'，并将名字歘过去
                    this.lastTypingTime = (new Date()).getTime();//记录当前的开始输入时间
    //                console.log(this.lastTypingTime);
                    setTimeout(function () {//这就是表示延时执行，即停止输入间隔this.TYPING_TIMER_LENGTH后执行停止输入事件
                        this.typingTimer = (new Date()).getTime();//记录当时停止输入的时间
    //                    console.log(this.typingTimer);
                        this.timeDiff = this.typingTimer - this.lastTypingTime;
    //                    console.log(this.timeDiff);
                        if (this.timeDiff >= this.TYPING_TIMER_LENGTH) {//判断实际间隔时间达到了设置的间隔没有，如果达到了就执行
                            this.$socket.emit('stopTyping');//当达到了停止的条件，就向后台发送停止输入的事件
                        }
                    }.bind(this),this.TYPING_TIMER_LENGTH)//表示具体延时多长时间执行，即多长时间(this.TYPING_TIMER_LENGTH)后执行
    
                },
                sendMessage() {//实现提交后保存消息到数据库
                    axios.post('/messages',{message:this.content}).then((resp) => {
    //                    console.log(resp.data);
    //                    this.message = {created_at:resp.data.created_at,message:resp.data.message,user_id:resp.data.user_id,user:this.Luser};
    //                    this.messages.push(this.message);
                        this.content='';
                    });
                },
                getMessages() {//从数据库获取当前所有的聊天信息
                    axios.get('/messages').then((resp) => {
    //                    console.log(resp.data);
                        this.messages = resp.data;
                    })
                }
            }
        }
    </script>
#### ②、Members.vue的具体内容为(即当前所有登陆用户信息)：
    
### 5、到resources->assets->js->app.js里面注册chat.vue,members.vue:
    Vue.component('chat', require('./components/Chat.vue'));
### 6、执行npm run watch