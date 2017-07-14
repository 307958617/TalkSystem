<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile');
    }

    public function avatarUpload(Request $request)
    {
        $data = $request->input('img');//这里的img就是UploadAvatar.vue通过axios上传来的通过croppie处理后的图片数据
        list($type,$data) = explode(';',$data);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);//对数据图形数据进行处理后进行base64解码，使之成为图片
        $imageName = time().'.png';//给图片另外命名
        $path = storage_path('app/public/images/avatars/');//使之图片的保存地址
        if(!file_exists($path)){//如果文件夹不存在就创建一个文件夹
            mkdir($path,0755,true);
        }
        file_put_contents($path.$imageName,$data);//将图片存储进指定的文件夹，并命名图片

        $imageUrl = asset('/storage/images/avatars').'/'.$imageName;//给刚刚存储进去的图片指定一个url地址，以便显示时调用
        $user = User::find(Auth::id());//找到当前用户
        $user->removeAvatar();//将当前用户原来存储的图片删除掉
        $user->avatar = $imageUrl;//将数据库的路径也替成新的图片路径
        $user->save();

        Redis::publish('user_image_upload',$imageUrl);//将图片地址传递给server.js后台，频道为：user_image_upload
        return response(['data'=> $user]);
    }
}
