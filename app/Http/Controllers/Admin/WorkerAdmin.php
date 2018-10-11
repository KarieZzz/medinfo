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

    public function fetch_units()
    {
        return \App\Medinfo\UnitTree::getSimpleTree(0, true);
    }

    public function fetch_worker_scopes($id)
    {
        if (!$id) {
            abort(500, 'No worker Id');
        }
        return WorkerScope::Worker($id)->get();
    }

    public function worker_store(Request $request)
    {
        //
        $this->validate($request, [
                'name' => 'required|unique:workers|max:24',
                'password' => 'required|max:16|min:4',
                'email' => 'email',
                'role' => 'required|digits:1',
                //'permission' => 'digits_between:3,4',
                'blocked' => 'required|in:1,0',
            ]
        );
        $worker = new Worker();
        $worker->name = $request->name;
        $worker->password = $request->password;
        $worker->email = $request->email;
        $worker->description = $request->description;
        $worker->role = (int)$request->role;
        $worker->permission = $this->setPermission($worker->role);
        $worker->blocked = $request->blocked;
        try {
            $worker->save();
            return ['message' => 'Новая запись создана. Id:' . $worker->id, 'id' => $worker->id];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            switch ($errorCode) {
                case '23505':
                    $message = 'Запись не сохранена. Дублирующиеся значения (' . $errorCode . ').';
                    break;
                default:
                    $message = 'Новая запись не создана. Код ошибки ' . $errorCode . '.';
                    break;
            }
            return ['error' => 422, 'message' => $message];
        }
    }

    public function worker_update(Worker $worker, Request $request)
    {
        $this->validate($request, [
                'name' => 'required|max:24',
                'password' => 'required|max:16|min:4',
                'email' => 'email',
                'role' => 'required|digits:1',
                //'permission' => 'digits_between:3,4',
                'blocked' => 'required|in:1,0',
            ]
        );
        //$worker = Worker::find($request->id);
        $worker->name = $request->name;
        $worker->password = $request->password;
        $worker->email = $request->email;
        $worker->description = $request->description;
        $worker->role = (int)$request->role;
        $worker->permission = $this->setPermission($worker->role);
        $worker->blocked = $request->blocked;
        try {
            $worker->save();
            return ['message' => 'Запись id ' . $worker->id . ' сохранена.'];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            switch ($errorCode) {
                case '23505':
                    $message = 'Запись не сохранена. Дублирующиеся значения (' . $errorCode . ').';
                    break;
                default:
                    $message = 'Новая запись не создана. Код ошибки ' . $errorCode . '.';
                    break;
            }
            return ['error' => 422, 'message' => $message];
        }
    }

    public function setPermission($role)
    {
        $permission = 0;
        switch ($role) {
            case 1 :
                $permission =
                    config('medinfo.permission.permission_read_report') +
                    config('medinfo.permission.permission_edit_report') +
                    config('medinfo.permission.permission_set_status_prepared');
                break;
            case 2 :
                $permission =
                    config('medinfo.permission.permission_read_report') +
                    config('medinfo.permission.permission_audit_document');
                break;
            case 3 :
                $permission =
                    config('medinfo.permission.permission_read_report') +
                    config('medinfo.permission.permission_edit_prepared_report') +
                    config('medinfo.permission.permission_edit_accepted_report') +
                    config('medinfo.permission.permission_set_status_accepted_declined');
                break;
            case 4 :
                $permission =
                    config('medinfo.permission.permission_read_report') +
                    config('medinfo.permission.permission_edit_prepared_report') +
                    config('medinfo.permission.permission_edit_accepted_report') +
                    config('medinfo.permission.permission_edit_aggregated_report') +
                    config('medinfo.permission.permission_set_status_accepted_declined') +
                    config('medinfo.permission.permission_set_status_approved');
                break;
            case 0 :
                $permission =
                    config('medinfo.permission.permission_read_report') +
                    config('medinfo.permission.permission_edit_report') +
                    config('medinfo.permission.permission_edit_prepared_report') +
                    config('medinfo.permission.permission_edit_accepted_report') +
                    config('medinfo.permission.permission_edit_approved_report') +
                    config('medinfo.permission.permission_edit_aggregated_report') +
                    config('medinfo.permission.permission_change_any_status') +
                    config('medinfo.permission.permission_set_status_prepared') +
                    config('medinfo.permission.permission_set_status_accepted_declined') +
                    config('medinfo.permission.permission_set_status_approved') +
                    config('medinfo.permission.permission_audit_document');
                break;
        }
        return $permission;
    }

    public function worker_scope_update(Request $request)
    {
        $this->validate($request, [
            'workerid' => 'required',
            'newscope' => 'required',
        ]);
        $newScope = explode(",", $request->newscope);
        $currentScope = WorkerScope::Worker($request->workerid)->pluck('ou_id')->toArray();
        $unitedScope = array_merge($currentScope, $newScope);
        $unitedScope = array_unique($unitedScope);
        $areRemoved = array_diff($unitedScope, $newScope);



        WorkerScope::Worker($request->workerid)->whereIn('ou_id', $areRemoved)->delete();

        if ($currentScope) {
            $currentScope->ou_id = $request->newscope;
            $currentScope->save();
            $comment = 'Учреждение/территория, к которому  имеет доступ пользователь, обновлено';
        } else {
            $new_scope = new WorkerScope();
            $new_scope->worker_id = $request->userid;
            $new_scope->ou_id = $request->newscope;
            $new_scope->with_descendants = 1;
            $new_scope->save();
            $comment = 'Учреждение/территория, к которому  имеет доступ пользователь, введено'  ;
        }
        return compact('comment');
    }

    public function worker_delete(Worker $worker)
    {
        $id = $worker->id;
        $scope_deleted = WorkerScope::Worker($id)->delete();
        $worker_deleted = $worker->delete();
        if ($worker_deleted) {
            $message = 'Удалена пользователь Id ' . $id;
        } else {
            $message = 'Ошибка удаления пользователя Id ' . $id;
        }
        return compact('worker_deleted', 'scope_deleted', 'message');
    }

}
