<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    //
    protected $fillable = ['name', 'password', 'email', 'description', 'role', 'permission', 'blocked'];
    protected $hidden = ['password'];

    public function worker_scopes()
    {
        return $this->hasMany('App\WorkerScope');
    }

    public function scopeOfRole($query, $type)
    {
        return $query->where('role', $type);
    }

    public function scopeExperts($query)
    {
        return $query->where('role', 2);
    }

    public function scopeExecutors($query)
    {
        return $query->where('role', 1);
    }

    public static function getExecutorEmails(array $ou_ids)
    {
        //$res = \DB::select("SELECT w.email FROM worker_scopes ws JOIN workers w ON ws.worker_id = w.id
        //            JOIN mo_hierarchy h ON ws.ou_id = h.id WHERE w.role = 1 AND w.email <> '' AND h.id in ($in)");
        $emails = \DB::table('workers')
            ->join('worker_scopes', 'worker_scopes.worker_id' ,'=', 'workers.id')
            ->join('mo_hierarchy', 'mo_hierarchy.id', '=', 'worker_scopes.ou_id')
            ->where('workers.role', 1)
            ->where('workers.blocked', '<>', 1)
            ->where('workers.email', '<>', '')
            ->whereIn('mo_hierarchy.id', $ou_ids)
            ->pluck('workers.email');
        //foreach ($res as $r) {
        //  $executors[] = $r->email;
        //}
        return array_unique($emails);
    }
}
