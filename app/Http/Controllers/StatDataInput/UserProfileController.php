<?php

namespace App\Http\Controllers\StatDataInput;

use App\WorkerProfile;
use App\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class UserProfileController extends Controller
{
    //
    public function index()
    {

    }

    public function create()
    {

    }

    public function store()
    {

    }

    public function show()
    {

    }

    public function edit($userprofile)
    {
        //$worker_id = Auth::guard('datainput')->id();
        $worker = Worker::find($userprofile);
        $email = $worker->email;
        $description = $worker->description;
        $wtel = WorkerProfile::firstOrCreate(['worker_id' => $worker->id, 'tag' => 'tel', 'attribute' => 'working'])->value;
        $ctel = WorkerProfile::firstOrCreate(['worker_id' => $worker->id, 'tag' => 'tel', 'attribute' => 'cell'])->value;
        $lastname = WorkerProfile::firstOrCreate(['worker_id' => $worker->id, 'tag' => 'lastname', 'attribute' => ''])->value;
        $firstname = WorkerProfile::firstOrCreate(['worker_id' => $worker->id, 'tag' => 'firstname', 'attribute' => ''])->value;
        $patronym = WorkerProfile::firstOrCreate(['worker_id' => $worker->id, 'tag' => 'patronym', 'attribute' => ''])->value;
        $post = WorkerProfile::firstOrCreate(['worker_id' => $worker->id, 'tag' => 'post', 'attribute' => ''])->value;
        $ou = WorkerProfile::firstOrCreate(['worker_id' => $worker->id, 'tag' => 'ou', 'attribute' => ''])->value;

        return compact('email','description',
            'lastname',
            'firstname',
            'patronym',
            'wtel',
            'ctel',
            'post',
            'ou'
        );
    }

    public function update($userprofile, Request $request)
    {
        $worker = Worker::find($userprofile);
        if (!$worker) {
            ['saved' => false];
        }
        $profiles = $this->getProfiles($worker->id);
        foreach ($profiles as $tag => $profile) {
            //$profile->value = trim($request->$tag);
            $profile->value = $request->$tag;
            $profile->save();
        }
        $worker->email = $request->email;
        $worker->description = $request->description;
        $worker->save();
        return ['saved' => true];
    }

    public function destroy()
    {

    }

    public function getProfiles($worker_id)
    {
        $lastname = WorkerProfile::firstOrCreate(['worker_id' => $worker_id, 'tag' => 'lastname', 'attribute' => '']);
        $firstname = WorkerProfile::firstOrCreate(['worker_id' => $worker_id, 'tag' => 'firstname', 'attribute' => '']);
        $patronym = WorkerProfile::firstOrCreate(['worker_id' => $worker_id, 'tag' => 'patronym', 'attribute' => '']);
        $wtel = WorkerProfile::firstOrCreate(['worker_id' => $worker_id, 'tag' => 'tel', 'attribute' => 'working']);
        $ctel = WorkerProfile::firstOrCreate(['worker_id' => $worker_id, 'tag' => 'tel', 'attribute' => 'cell']);
        $post = WorkerProfile::firstOrCreate(['worker_id' => $worker_id, 'tag' => 'post', 'attribute' => '']);
        $ou = WorkerProfile::firstOrCreate(['worker_id' => $worker_id, 'tag' => 'ou', 'attribute' => '']);
        return compact('lastname','firstname','patronym', 'wtel', 'ctel', 'post', 'ou' );
    }
}
