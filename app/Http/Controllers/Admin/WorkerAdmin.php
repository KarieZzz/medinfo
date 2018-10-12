<?php

namespace App\Http\Controllers\Admin;

use App\DocumentMessage;
use App\RecentDocument;
use App\ValuechangingLog;
use App\WorkerSetting;
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
        $this->validate($request, [
                'user_name' => 'required|max:24',
                'password' => 'required|max:16|min:4',
                'email' => 'email',
                'role' => 'required|digits:1',
                //'permission' => 'digits_between:3,4',
                'blocked' => 'required|in:1,0',
            ]
        );
        $worker = new Worker();
        $worker->name = $request->user_name;
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
                'user_name' => 'required|max:24',
                'password' => 'required|max:16|min:4',
                'email' => 'email',
                'role' => 'required|digits:1',
                //'permission' => 'digits_between:3,4',
                'blocked' => 'required|in:1,0',
            ]
        );
        //$worker = Worker::find($request->id);
        $worker->name = $request->user_name;
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
            'worker' => 'required',
            'newscope' => 'required',
        ]);
        $newScope = explode(",", $request->newscope);
        if (in_array('0', $newScope)) {
            $sc = WorkerScope::firstOrNew(['worker_id' => $request->worker]);
            $sc->ou_id = 0;
            $sc->save();
            $scope_deleted = WorkerScope::Worker($request->worker)->where('ou_id','<>' ,0)->delete();
            $message = 'Установлен доступ ко всем организационным единицам';
            return compact('message', 'scope_deleted');
        }
        $currentScope = WorkerScope::Worker($request->worker)->pluck('ou_id')->toArray();
        $unitedScope = array_merge($currentScope, $newScope);
        $unitedScope = array_unique($unitedScope);
        $pureList = array_intersect($unitedScope, $newScope);

        //dd($pureList);

        foreach ($pureList as $ou) {
            $sc = WorkerScope::Worker($request->worker)->firstOrCreate(['worker_id' => $request->worker,'ou_id' => $ou, 'with_descendants' => 1]);
            //$sc->save();
        }
        $deleted = WorkerScope::Worker($request->worker)->whereNotIn('ou_id', $pureList)->delete();
        $saved = count($pureList);
        $message = 'Список доступа обновлен. Включено ' . $saved . ' ОЕ, удалено - ' . $deleted;
        return compact('message','saved', 'deleted');
    }

    public function worker_delete(Worker $worker)
    {
        $id = $worker->id;
        $scope_deleted = WorkerScope::Worker($id)->delete();
        $recent_deleted = RecentDocument::OfWorker($id)->delete();
        $valchangelog_deleted = ValuechangingLog::OfWorker($id)->delete();
        $wsettings_deleted = WorkerSetting::OfWorker($id)->delete();
        $docmessages_deleted = DocumentMessage::OfWorker($id)->delete();
        $worker_deleted = $worker->delete();
        if ($worker_deleted) {
            $message = 'Удалена пользователь Id ' . $id;
        } else {
            $message = 'Ошибка удаления пользователя Id ' . $id;
        }
        return compact('worker_deleted', 'scope_deleted', 'recent_deleted', 'valchangelog_deleted', 'wsettings_deleted', 'docmessages_deleted','message');
    }

}
