<?php

namespace App\Medinfo;

use DB;

//require_once 'table.php';
// MM - Medinfo Model Class
class FormMM
{
    public $form_id;
    private $formCode;
    private $formName;
    private $medstatCode;
    private $file_name;
    private $_tables = array();
    private $_tablecodes = array();
    private $_activeTableIndex;
    private $dba; // Подключение Mysql
    
    public function __construct($fIndex = null) 
    {
        if (!$fIndex) {
            throw new Exception("Идентификатор формы не определен");
        }
        $this->form_id = $fIndex;

        $query = "SELECT * FROM forms WHERE id = $fIndex";
        $a = DB::selectOne($query);
        $this->formCode = $a->form_code;
        $this->formName = $a->form_name;
        $this->medstatCode = $a->medstat_code;
        $this->file_name = $a->file_name;
        //$this->_fill_tableCollection();
    }


    /*    public function __toString()
        {
            return $this->_formName;
        }*/

    public static function getFormByCode($fCode)
    {
        $q = "SELECT id FROM forms WHERE form_code = '$fCode'";
        $res = DB::selectOne($q);
        $form_id = $res[0];
        if (!$form_id) {
            throw new Exception("Форма $fCode не существует");
        }
        $form = new FormMM($form_id);
        return $form;
    }

    public static function getFormAuditors($form_id) {
        $query = "select user_id from mi_form_auditors where form_id = $form_id";
        $db = DB_ll::getInstance();
        $dba = $db::$dbh;
        $a_exec = $dba->query($query);
        $auditors = array();
        while ($res = $a_exec->fetch_row()) {
            $auditors[] = $res[0];
        }
        return $auditors;
    }

    public function getTablebyCode($code)
    {
        foreach ($this->_tables as $table) {
            if ($table->getCode() == $code) {
                return $table;
            }
        }
        return null;
    }

    public function getName()
    {
        return $this->formName;
    }
    public function getMedstaCode()
    {
        return $this->medstatCode;
    }

    public function getCode()
    {
        return $this->formCode;
    }

    public function getFileName()
    {
        return $this->file_name;
    }
    
    public function getTableCollection()
    {
        return $this->_tables;
    }

    private function _fill_tableCollection()
    {
        $tq = "SELECT * FROM tables WHERE form_id = {$this->form_id} and deleted = 0 ORDER BY CAST(table_code AS UNSIGNED)";
        $t_exec= $this->dba->query($tq);
        while ($t = $t_exec->fetch_object()) {
            $this->_tables[$t->table_id] = new Table($t->table_id);
            $this->_tables[$t->table_id]->setCode($t->table_code);
            $this->_tables[$t->table_id]->setName($t->table_name);
        }
        //var_dump($this->_tables);
    }
}

?>