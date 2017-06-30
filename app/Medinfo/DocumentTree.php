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
    private $filter_mode = 1;
    private $worker_scope;
    private $o_units = [];
    private $states = array();
    private $monitorings = array();
    private $forms = array();
    private $periods = array();
    private $dtypes = array();
    private $scopes = array();
    private $documents = array();

    public function __construct($scopes = null)
    {
        //dd($scopes);
        if (is_array($scopes)) {
            if ($scopes['top_node'] == 'null') {
                throw new \Exception("Не определен перечень доступа к медицинским организациям");
            }
            $this->top_node = (int)$scopes['top_node'];
            $this->worker_scope = (int)$scopes['worker_scope'];
            //dd($this->worker_scope);
            if (isset($scopes['filter_mode'])) {
                $this->filter_mode = $scopes['filter_mode'];
            }
            if (isset($scopes['states'])) {
                $this->states = $scopes['states'];
            }
            if (isset($scopes['monitorings'])) {
                $this->monitorings = $scopes['monitorings'];
            }
            if (isset($scopes['forms'])) {
                $this->forms = $scopes['forms'];
            }
            if (isset($scopes['periods'])) {
                $this->periods = $scopes['periods'];
            }
            if (isset($scopes['dtypes'])) {
                $this->dtypes = $scopes['dtypes'];
            }
        }
        else {
            echo("Не определены условия выборки документов");
            throw new \Exception("Не определены условия выборки документов");
        }
        $this->setScopes();
        $this->get_descendants();
    }

    public function setScopes() {
        if (count($this->dtypes) > 0 ) {
            $this->scopes['t'] = !empty(implode(",", $this->dtypes)) ?  ' AND d.dtype in (' . implode(",", $this->dtypes) . ')' : ' AND d.type = 0 ';
        }
        if (count($this->states) > 0 ) {
            $this->scopes['s'] = !empty(implode(",", $this->states)) ? ' AND d.state in (' . implode(",", $this->states) . ')' : ' AND d.state = 0';
        }
        if (count($this->forms) > 0 ) {
            $this->scopes['f'] = !empty(implode(",", $this->forms)) ?  ' AND f.id in (' . implode(",", $this->forms) . ')' : ' AND f.id = 0 ';
        }
        if (count($this->monitorings) > 0 ) {
            $this->scopes['m'] = !empty(implode(",", $this->monitorings)) ?  ' AND m.id in (' . implode(",", $this->monitorings) . ')' : ' AND m.id = 0 ';
        }
        if (count($this->periods) > 0 ) {
            $this->scopes['p'] = !empty(implode(",", $this->periods)) ?  ' AND d.period_id in (' . implode(",", $this->periods) . ')' : ' AND d.period_id = 0 ';
        }
    }

    public function get_descendants()
    {
        $this->by_territory($this->worker_scope);
        if ($this->filter_mode == 1) {
            $this->by_territory($this->top_node);
        } elseif ($this->filter_mode == 2) {
            $this->by_groups();
        } else {
            throw new \Exception("Недопустимый режим выбора документов");
        }
    }

    public function by_territory($node)
    {
        $units = [];
        if ($node !== 0) {
            $units[] = $node;
            $units = array_merge($units, $this->tree_element($node));
            if (count($units) > 0) {
                $strigified = implode(",", $units);
                $this->scopes['u'] = " AND d.ou_id in ($strigified) ";
            } else {
                $this->scopes['u'] = " AND 1=0 ";
            }
        }
    }

    public function by_groups()
    {
        $units = \App\UnitGroupMember::OfGroup($this->top_node)->pluck('ou_id');
        //dd($units);
        if (count($units) > 0) {
            $strigified = $units->implode(',');
            $this->scopes['g'] = " AND d.ou_id in ($strigified) ";
        } else {
            $this->scopes['g'] = " AND 1=0 ";
        }
    }

    public function getUnits()
    {
        return $this->o_units;
    }

    private function tree_element($parent) {

        $lev_query = "SELECT id FROM mo_hierarchy WHERE parent_id = $parent";
/*        $lev_query = "SELECT id FROM mo_hierarchy WHERE parent_id = $parent
          UNION SELECT id FROM unit_groups WHERE parent_id = $parent
          UNION SELECT ou_id FROM unit_group_members WHERE group_id = $parent";*/
/*        $lev_query = "
          SELECT ou_id AS id FROM unit_group_members WHERE group_id = $parent";*/
        //dd($lev_query);
        $res = DB::select($lev_query);
        $units = [];
        //var_dump($res);
        if (count($res) > 0) {
            foreach ($res as $r) {
                $units[] = $r->id;
                $units = array_merge($units, $this->tree_element($r->id));
            }
        }
        //var_dump($units);
        return $units;
    }

    public function get_documents()
    {
        //dd($this->scopes);
        if (count($this->scopes) > 0 ) {
            $scopes = implode(" ", $this->scopes);
            $doc_query = "SELECT d.id, u.unit_code, u.unit_name, f.form_code,
              f.form_name, s.name state, m.name monitoring, p.name period, t.name doctype, a.protected,
              CASE WHEN (SELECT sum(v.value) FROM statdata v where d.id = v.doc_id) > 0 THEN 1 ELSE 0 END filled
              FROM documents d
                JOIN forms f on d.form_id = f.id
                LEFT JOIN mo_hierarchy u ON d.ou_id = u.id
                JOIN dic_document_states s ON d.state = CAST(s.code AS numeric)
                JOIN dic_document_types t ON d.dtype = CAST(t.code AS numeric)
                JOIN monitorings m ON d.monitoring_id = m.id
                JOIN periods p ON d.period_id = p.id
                LEFT JOIN aggregates a ON a.doc_id = d.id
              WHERE 1=1 $scopes
              GROUP BY d.id, u.unit_code, u.unit_name, f.form_code, f.form_name, m.name, p.name, s.name, t.name, a.protected
              ORDER BY u.unit_code, f.form_code, p.name";
            //echo $doc_query;
            $this->documents = DB::select($doc_query);
            if ($this->filter_mode == 2 ) {
                $group_doc_query = "SELECT d.id, u.group_code AS unit_code, u.group_name AS unit_name, f.form_code,
                  f.form_name, s.name state, m.name monitoring, p.name period, t.name doctype, a.protected,
                  CASE WHEN (SELECT sum(v.value) FROM statdata v where d.id = v.doc_id) > 0 THEN 1 ELSE 0 END filled
                  FROM documents d
                    JOIN forms f on d.form_id = f.id
                    LEFT JOIN unit_groups u on d.ou_id = u.id
                    JOIN dic_document_states s on d.state = CAST(s.code AS numeric)
                    JOIN dic_document_types t on d.dtype = CAST(t.code AS numeric)
                    JOIN monitorings m ON d.monitoring_id = m.id
                    JOIN periods p on d.period_id = p.id
                    LEFT JOIN aggregates a ON a.doc_id = d.id
                  WHERE d.ou_id = {$this->top_node} {$this->scopes['t']} {$this->scopes['m']} {$this->scopes['f']} {$this->scopes['p']} {$this->scopes['s']}
                  GROUP BY d.id, u.group_code, u.group_name, f.form_code, f.form_name, m.name, p.name, s.name, t.name, a.protected
                  ORDER BY f.form_code, p.name";
                //echo $group_doc_query;
                $documents_by_groups = DB::select($group_doc_query);
                //dd($documents_by_groups );
                $this->documents = array_merge($this->documents, $documents_by_groups);
            }
            //dd($this->documents);
            return $this->documents;
        }
        else {
            return null;
        }
    }

    public function get_aggregates()
    {
        //dd($this->scopes);
        //$aggregates = array();
        if (count($this->scopes) > 0 ) {
            $scopes = implode(" ", $this->scopes);
            $doc_query = "SELECT d.id, u.unit_code, u.unit_name,  m.name monitoring, f.form_code, f.form_name, p.name period, a.aggregated_at,
                CASE WHEN (SELECT sum(v.value) FROM statdata v WHERE d.id = v.doc_id) > 0 THEN 1 ELSE 0 END filled
              FROM documents d
              LEFT JOIN forms f on d.form_id = f.id
              LEFT JOIN mo_hierarchy u on d.ou_id = u.id
              LEFT JOIN aggregates a ON d.id = a.doc_id
              JOIN monitorings m ON d.monitoring_id = m.id
              JOIN periods p on d.period_id = p.id
              WHERE 1=1 $scopes ORDER BY u.unit_code, f.form_code, p.name";
            //dd($doc_query);
            $res = DB::select($doc_query);

            if ($this->filter_mode == 2 ) {
                $group_doc_query = "SELECT d.id, u.group_code AS unit_code, u.group_name AS unit_name,  m.name monitoring, f.form_code, f.form_name, p.name period, a.aggregated_at,
                    CASE WHEN (SELECT sum(v.value) FROM statdata v WHERE d.id = v.doc_id) > 0 THEN 1 ELSE 0 END filled
                  FROM documents d
                  LEFT JOIN forms f on d.form_id = f.id
                  LEFT JOIN unit_groups u on d.ou_id = u.id
                  LEFT JOIN aggregates a ON d.id = a.doc_id
                  JOIN monitorings m ON d.monitoring_id = m.id
                  JOIN periods p on d.period_id = p.id
                   WHERE d.ou_id = {$this->top_node} {$this->scopes['m']} {$this->scopes['f']} {$this->scopes['p']}
                   ORDER BY f.form_code, p.name";
                $aggregates_by_groups = DB::select($group_doc_query);
                //dd($documents_by_groups );
                $res = array_merge($res, $aggregates_by_groups);

            }
            return $res;
        }
        else {
            return null;
        }
    }
}