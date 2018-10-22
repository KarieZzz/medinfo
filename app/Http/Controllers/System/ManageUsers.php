<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;

class ManageUsers extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function setTokens()
    {
        $users = User::all();
        foreach ($users as $user) {
            $user->api_token = str_random(60);
            $user->save();
        }
        return ['result' => 'users managed', 'count' => $users->count()  ];
    }

}
