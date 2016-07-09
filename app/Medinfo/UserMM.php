<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 27.06.2016
 * Time: 17:59
 */

namespace app\Medinfo;
use DB;

class UserMM
{
    public $user_id;
    private $name;
    private $pwd;
    private $email;
    private $description;
    private $role;
    private $permission;
    private $blocked;
    private $dba;
    public static $disabled_states = array(
        0 => '',
        1 => "'performed', 'accepted', 'declined', 'approved'",
        2 => "'performed', 'prepared', 'accepted', 'declined', 'approved'",
        3 => "'performed', 'prepared', 'approved'",
        4 => "'performed', 'prepared'"
    );

    public function __construct($uid = null)
    {
        if (!$uid) {
            return;
        }
        else {
            $res = DB::selectOne("SELECT * FROM workers WHERE id = '$uid'");
            if($res) {
                $this->user_id = $res->id;
                $this->name = $res->name;
                $this->pwd = $res->pwd;
                $this->email = $res->email;
                $this->description = $res->description;
                $this->role = $res->role;
                $this->permission = $res->permission;
                $this->blocked = $res->blocked;
            }
            else {
                throw new Exception("Пользователь c id $uid не найден!");
            }
        }
    }
    public static function getUserByName($user_name){
        if (!$user_name) {
            throw new Exception("Не указано имя пользователя");
        }
        $res = DB::selectOne("SELECT id FROM workers WHERE name = '$user_name'");
        if (!$res) {
            throw new Exception("Пользователь с данным именем не найден");
        }
        return new UserMM($res->id);
    }

    public function getScope()
    {
        if (!$this->user_id) {
            return null;
        }
        $res = DB::selectOne("select ou_id from worker_scopes WHERE worker_id = {$this->user_id}");
        if (!$res) {
            return null;
        }
        else {
            return $res->ou_id;
        }
    }

/*    public function store()
    {
        $query = "INSERT INTO users (`name`, pwd, email, description, role, permission, blocked) VALUES
          ('$this->name', '$this->pwd', '$this->email', '$this->description', '$this->role', '$this->permission', '$this->blocked')
           ON DUPLICATE KEY UPDATE `name` = '$this->name', pwd = '$this->pwd', email = '$this->email', description = '$this->description',
           role = '$this->role', permission = '$this->permission', blocked = '$this->blocked'" ;
        $this->dba->query($query);
        if ($this->dba->errno <> 0) {
            $affected = false;
        }
        else {
            $affected = $this->dba->affected_rows;
            if ($this->dba->insert_id <> 0) {
                $this->user_id = $this->dba->insert_id;
            }
        }
        return $affected;
    }*/

/*    public function update()
    {
        if (!$this->user_id) {
            throw new Exception("Для обновления записи пользователя необходим uid");
        }
        $query = "UPDATE users SET `name` = '$this->name', pwd = '$this->pwd', email = '$this->email', description = '$this->description',
          role = '$this->role', permission = '$this->permission', blocked = '$this->blocked' where uid = {$this->user_id}" ;
        $this->dba->query($query);
        return $this->dba->affected_rows;
    }*/

    public function getName()
    {
        return $this->name;
    }
    public function getPassword()
    {
        return $this->pwd;
    }
    public function getEmail() {
        return $this->email;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function getRole()
    {
        return $this->role;
    }
    public function getPermission()
    {
        return $this->permission;
    }
    public function isBlocked()
    {
        return (bool)$this->blocked;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setPwd($pwd)
    {
        $this->pwd = $pwd;
    }
    public function setEmail($email)
    {
        $this->email = $email;
    }
    public function setDescription($description)
    {
        $this->description = $description;
    }
    public function setRole($role)
    {
        $this->role = $role;
    }
    public function setPermisssion($permission)
    {
        $this->permission = $permission;
    }
    public function setBlocked($blocked)
    {
        $this->blocked = (int)$blocked;
    }
}