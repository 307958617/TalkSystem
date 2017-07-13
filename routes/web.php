<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/profile', 'ProfileController@edit')->name('edit.profile');
Route::post('/avatar-upload', 'ProfileController@avatarUpload')->name('avatar.upload');

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/messages',function (){
    return $messages = \App\Message::with('user')->get();
})->middleware('auth');

Route::post('/messages',function (){
    $user = Auth::user();
    $message = $user->messages()->create([
        'message' => request()->get('message')
    ]);
//    $redis = Redis::connection();
//    Redis::publish('chatroom',$message);
    broadcast(new \App\Events\MessagePosted($message,$user))->toOthers();
    return $message;
});
