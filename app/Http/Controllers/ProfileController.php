<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
    }
}
