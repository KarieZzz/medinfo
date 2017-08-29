<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;

class UserAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function index()
    {
        return view('jqxadmin.users');
    }

    public function fetchUsers()
    {
        return User::orderBy('id')->get();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|min:4',
            ]
        );
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = \Hash::make($request->password);
        $user->save();
        return ['Новый пользователь добавлен'];
    }

    public function update(User $user, Request $request)
    {
        $this->validate($request, [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255',
                'password' => 'min:4',
            ]
        );
        if (!empty($request->password)) {
            $user->password = \Hash::make($request->password);
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        return ['Изменения сохранены'];
    }

    public function destroy(User $user)
    {
        $user->delete();
        return ['Пользователь удален'];
    }


}
