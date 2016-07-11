<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 29.06.2016
 * Time: 15:15
 */

namespace App\Medinfo;
use DB;


class DocumentTree
{
    private $top_node;
    private $o_units = array();
    private $states = array();
    private $forms = array();
    private $periods = array();
    private $scopes = array();
    private $documents = array();

    public function __construct($scopes = null)
    {
        if (is_array($scopes)) {
            $this->top_node = $scopes['top_node'];
            if ($this->top_node == 'null') {
                throw new Exception("Не определен перечень доступа к медицинским организациям");
            }
            if (isset ($scopes['states'])) {
                $this->states = $scopes['states'];
            }
            if (isset($scopes['forms'])) {
                $this->forms = $scopes['forms'];
            }
            if (isset($scopes['periods'])) {
                $this->periods = $scopes['periods'];
            }

        }
        else {
            echo("Не определены условия выборки документов");
            throw new Exception("Не определены условия выборки документов");
        }
        $this->setScopes();
        $this->get_descendants();
    }

    public function setScopes() {
        $s = array();
        $f = array();
        $p = array();
        foreach($this->states as $state) {
            $s[] = substr($state, 2);
        }
        foreach($this->forms as $form) {
            $f[] = substr($form, 1);
        }
        foreach($this->periods as $period) {
            $p[] = substr($period, 1);
        }
        if (count($s) > 0 ) {
            $this->scopes[] = 'and d.state in (' . implode(",", $s) . ')';
        }
        if (count($f) > 0 ) {
            $this->scopes[] = "and f.form_code in ('" . implode("','", $f) . "')";
        }
        $this->scopes[] = "and d.period_id in ('" . implode("','", $p) . "')";
    }

    public function get_descendants()
    {
        if ($this->top_node === '0') {
            $lev_1_query = "select id from mo_hierarchy where parent_id is NULL";
            $res = DB::selectOne($lev_1_query);
            $this->o_units[] = $res->id;
            $this->o_units = array_merge($this->o_units, $this->tree_element($res->id));
        }
        else {
            $this->o_units[] = $this->top_node;
            $this->o_units = array_merge($this->o_units, $this->tree_element($this->top_node));
        }
        //var_dump($this->o_units);
        return $this->o_units;
    }

    public function getUnits()
    {
        return $this->o_units;
    }

    private function tree_element($parent) {
        $lev_query = "select id from mo_hierarchy where parent_id = $parent";
        //echo $lev_query;
        $res = DB::select($lev_query);
        $units = array();
        if (count($res) > 0) {
            foreach ($res as $r) {
                $units[] = $r->id;
                $units = array_merge($units, $this->tree_element($r->id));
            }
        }
        return $units;
    }

    public function get_documents()
    {
        if (count($this->o_units) > 0) {
            $unit_scope = '';
            if ($this->top_node !== '0') {
                $strigified = implode(",", $this->o_units);
                $unit_scope .= "and d.ou_id in ($strigified)";
            }
            $scopes = '';
            if (count($this->scopes) > 0 ) {
                $scopes = implode(" ", $this->scopes);
            }
            $doc_query = "SELECT DISTINCT ON(u.unit_code, f.form_code) d.id, u.unit_code, u.unit_name, f.form_code, f.form_name, s.name state, p.name period,
            CASE  WHEN (SELECT sum(v.value) FROM primary_statdata v where d.id = v.doc_id) > 0 THEN 1 ELSE 0 END filled
              FROM documents d
                JOIN forms f on d.form_id = f.id
                JOIN mo_hierarchy u on d.ou_id = u.id
                JOIN dic_document_states s on d.state = CAST(s.code AS numeric)
                JOIN dic_periods p on d.period_id = p.code
              WHERE 1=1 $unit_scope $scopes
              GROUP BY d.id, u.unit_code, u.unit_name, f.form_code, f.form_name, s.name, p.name
              ORDER BY u.unit_code, f.form_code, d.period_id;";
            //echo $doc_query;
            $res = DB::select($doc_query);
            $this->documents = $res;
            /*$i = 0;
            while ($r = $res->fetch_assoc()) {
                $this->documents[$i] = $r;
                $i++;
            }*/
            return $this->documents;
        }
        else {
            return null;
        }
    }

    public function get_aggregates()
    {
        $aggregates = array();
        if (count($this->o_units) > 0) {
            $unit_scope = '';
            if ($this->top_node !== '0') {
                $strigified = implode(",", $this->o_units);
                $unit_scope .= "and d.ou_id in ($strigified)";
            }
            $scopes = '';
            if (count($this->scopes) > 0 ) {
                $scopes = implode(" ", $this->scopes);
            }
            // TODO Разобраться как корректно выбрать актуальность сводного отчета;
            /*$doc_query = "select d.doc_id, u.unit_code, u.unit_name, f.form_code, f.form_name, p.name period,
                d.updated_at, max(v.updated_at) data_update_at,
                if( (d.updated_at > max(v.updated_at) or d.updated_at > max(vv.updated_at) ) , 1, 0) actual,
                if (isnull(max(v.updated_at)) , 1, 0) nodata, max(vv.updated_at) parented_ou
                from mi_aggregates d
                join mi_form f on d.form_id = f.form_id
                join mo_hierarchy u on d.ou_id = u.unit_id
                join dic_periods p on d.period_id = p.code
                left join mi_document ddd on d.ou_id = ddd.ou_id and d.period_id = ddd.period_id and d.form_id = ddd.form_id
                left join mo_hierarchy m on d.ou_id = m.parent_id
                left join mi_document dd on d.form_id = dd.form_id and d.period_id = dd.period_id and m.unit_id = dd.ou_id
                left join mi_data v on dd.doc_id = v.doc_id
                left join mi_data vv on ddd.doc_id = vv.doc_id
            where 1=1 $unit_scope $scopes
            group by d.doc_id
            ORDER BY u.unit_code, f.form_code, d.period_id";*/
            $doc_query = "SELECT d.id, u.unit_code, u.unit_name, f.form_code, f.form_name, p.name period, d.updated_at
              FROM aggregates d
              left join forms f on d.form_id = f.id
              left join mo_hierarchy u on d.ou_id = u.id
              join dic_periods p on d.period_id = p.code
              where 1=1 $unit_scope $scopes ORDER BY u.unit_code, f.form_code, d.period_id";
            //echo $doc_query;
            $res = DB::select($doc_query);
            return $res;
        }
        else {
            return null;
        }
    }

    public function get_filled_documents()
    {
        foreach ($this->periods as $period) {
            $edited_documents = array();
            $filled_docs_query = "SELECT d.nl2, t.table_code FROM $period d
              LEFT JOIN mi_table t on (d.nl2 = t.table_id)
              LEFT JOIN mi_form f on (t.form_id = f.form_id)
              LEFT JOIN mi_document doc on (f.form_id = doc.form_id)
              WHERE d.nl0 = {$lpu->ou_id} AND f.form_id = {$form->form_id} GROUP BY d.nl2";
            $filled_tables_exec = $this->dba->query($filled_docs_query);
            while ($row = $filled_tables_exec->fetch_row()) {
                $edited_documents[] = $row[0];
            }
        }
    }
}