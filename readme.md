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
    
### 5、到resources->assets->js->app.js里面注册chat.vue:
    Vue.component('chat', require('./components/Chat.vue'));
### 6、执行npm run watch