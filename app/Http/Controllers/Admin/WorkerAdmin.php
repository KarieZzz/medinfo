<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use App\Worker;
use App\WorkerScope;

class WorkerAdmin extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function index()
    {
        return view('jqxadmin.workers');
    }

    public function fetch_workers()
    {
        $workers = DB::select("select * from workers order by id");
        //$workers = Worker::orderBy('id')->get(); // возвращает без паролей
        return $workers;
        //return "{\"data\":" .json_encode($workers). "}";
    }

/*    public function fetch_mo_hierarchy()
    {
        $mo_tree = DB::select("select id, parent_id, unit_code, unit_name from mo_hierarchy where blocked = 0 ORDER BY unit_code");
        return $mo_tree;
    }*/

    public function fetch_worker_scopes($id)
    {
        $data = Array();
        if (!$id) {
            $data['scopes'] = false;
            $data['comment'] = "Не указан Id пользователя. Список учреждений предоставить не возможно";
            $responce_no_user['responce'] = $data;
            return $responce_no_user;
        }
        $scope = DB::selectOne("select w.ou_id, o.unit_code, o.unit_name from worker_scopes w join mo_hierarchy o
          on w.ou_id = o.id WHERE worker_id = {$id}");
        if (!$scope) {
            $data['scopes'] = 0;
            $data['comment'] = "Не указаны";
        } else {
            $data['scope'] = $scope->ou_id;
            $data['unit_code'] = $scope->unit_code;
            $data['unit_name'] = $scope->unit_name;
            $data['comment'] = $scope->unit_name;
        }
        $responce['responce'] = $data;
        return $responce;
    }

    public function worker_store(Request $request)
    {
        //
        $this->validate($request, [
                'name' => 'required|unique:workers|max:16',
                'password' => 'required|max:16|min:4',
                'email' => 'email',
                'role' => 'digits:1',
                'permission' => 'digits_between:3,4',
                'blocked' => 'required|in:1,0',
            ]
        );
        $worker = new Worker($request->all());
        $worker->save();
        $responce['responce']['comment'] = 'Новая запись создана. Id:' . $worker->id;  ;
        return $responce;
    }

    public function worker_update(Worker $worker, Request $request)
    {
        $this->validate($request, [
                'name' => 'required|max:16',
                'password' => 'required|max:16|min:4',
                'email' => 'email',
                'role' => 'digits:1',
                'permission' => 'digits_between:3,4',
                'blocked' => 'required|in:1,0',
            ]
        );
        //$worker = Worker::find($request->id);
        $worker->name = $request->name;
        $worker->password = $request->password;
        $worker->email = $request->email;
        $worker->description = $request->description;
        $worker->role = $request->role;
        $worker->permission = $request->permission;
        $worker->blocked = $request->blocked;
        $worker->save();
        $responce['responce']['comment'] = 'Запись с Id' . $worker->id . ' сохранена'  ;
        return $responce;
    }

    public function worker_scope_update(Request $request)
    {
        $this->validate($request, [
            'userid' => 'required',
            'newscope' => 'required',
        ]);
        //$ret = DB::insert("INSERT INTO worker_scopes (worker_id, ou_id)
          //VALUES ({$request->userid}, {$request->newscope})
          //ON CONFLICT (worker_id) DO UPDATE SET ou_id = EXCLUDED.ou_id;");
        $current_scope = WorkerScope::where('worker_id', $request->userid)->first();
        if ($current_scope) {
            $current_scope->ou_id = $request->newscope;
            $current_scope->save();
            $responce['responce']['comment'] = 'Учреждение/территория, к которому  имеет доступ пользователь, обновлено';
        } else {
            $new_scope = new WorkerScope();
            $new_scope->worker_id = $request->userid;
            $new_scope->ou_id = $request->newscope;
            $new_scope->with_descendants = 1;
            $new_scope->save();
            $responce['responce']['comment'] = 'Учреждение/территория, к которому  имеет доступ пользователь, введено'  ;
        }
        return $responce;
    }

    public function worker_delete(Worker $worker)
    {
        $id = $worker->id;
        $scope_deleted = WorkerScope::where('worker_id', $id)->delete();
        $worker_deleted = $worker->delete();
        if ($worker_deleted) {
            $message = 'Удалена пользователь Id ' . $id;
        } else {
            $message = 'Ошибка удаления пользователя Id ' . $id;
        }
        return compact('worker_deleted', 'scope_deleted', 'message');
    }

}
