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
        $data = $request->input('img');
        list($type,$data) = explode(';',$data);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);
        $imageName = time().'.png';
        $path = storage_path('app/public/images/avatars/');
        if(!file_exists($path)){
            mkdir($path,0755,true);
        }
        file_put_contents($path.$imageName,$data);

        $imageUrl = asset('/storage/images/avatars').'/'.$imageName;
        $user = User::find(Auth::id());
        $user->removeAvatar();
        $user->avatar = $imageUrl;
        $user->save();

        Redis::publish('user_image_upload',$imageUrl);
        return response(['data'=> $user]);
    }
}
