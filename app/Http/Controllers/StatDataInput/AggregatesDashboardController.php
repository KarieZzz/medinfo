<?php

namespace App\Http\Controllers\StatDataInput;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Unit;
use App\UnitGroupMember;
use App\Document;
use App\Aggregate;
use App\Row;
use App\Column;
use App\Cell;

class AggregatesDashboardController extends DashboardController
{
    //
    protected $dashboardView = 'jqxdatainput.aggregatedashboard';

    public function __construct()
    {
        $this->middleware('datainputauth');
    }

    public function aggregateData(Document $document, int $unitgroup)
    {
        $aggregate = Aggregate::find($document->id);
        $protected = is_null($aggregate) ? 0 : $aggregate->protected;
        $calc = Column::Calculated()->pluck('id')->toArray();
        $calc[] = 0;
        $calculatedColumns = implode(',', $calc);
        //dd($calculatedColumns);
        //echo implode(',', $calculatedColumns);
        if ($protected) {
            $result['aggregate_status'] = 500;
            $result['error_message'] =  'Данный документ защищен от повторного сведения';
            return $result;
        }
        $result = [];
        // перед сведением данных удаление старых данных
        Cell::where('doc_id', $document->id)->delete();
        if ($unitgroup == 1) {
            $units = Unit::getDescendants($document->ou_id);
        } else {
            $units = UnitGroupMember::OfGroup($document->ou_id)->pluck('ou_id');
        }
        //dd($units);
        $included_documents = Document::whereIn('ou_id', $units)
            ->where('dtype', 1)
            ->where('monitoring_id', $document->monitoring_id)
            ->where('form_id', $document->form_id)
            ->where('period_id', $document->period_id)
            ->pluck('id');
        $stringified_documents = implode(',', $included_documents->toArray());
        if (!$stringified_documents) {
            $result['aggregate_status'] = 500;
            $result['error_message'] =  'Нет первичных документов, содержащих данные, для сведения';
            return $result;
        }
        $now = Carbon::now();

        $query = "INSERT INTO statdata
            (doc_id, table_id, row_id, col_id, value, created_at, updated_at )
          SELECT '{$document->id}', v.table_id, v.row_id, v.col_id, SUM(value), '$now', '$now'  FROM statdata v
            JOIN documents d on v.doc_id = d.id
            JOIN tables t on (v.table_id = t.id)
            JOIN forms f on d.form_id = f.id
            JOIN mo_hierarchy h on d.ou_id = h.id
          WHERE d.id in ({$stringified_documents}) 
            AND h.blocked <> 1 
            AND v.col_id NOT IN ($calculatedColumns) 
          GROUP BY v.table_id, v.row_id, v.col_id";
        $affected_cells = \DB::select($query);
        $aggregate = Aggregate::firstOrCreate(['doc_id' => $document->id]);
        $aggregate->include_docs = $stringified_documents;
        $aggregate->aggregated_at = Carbon::now();
        $aggregate->save();
        $result['affected_cells'] = count($affected_cells);
        $result['aggregate_status'] = 200;
        $result['aggregated_at'] =  $aggregate->aggregated_at->toDateTimeString();
        return $result;
    }

    public function fetchAggregatedCellLayers(Document $document, Row $row, Column $column)
    {
        $aggregate = Aggregate::find($document->id);
        //dd($aggregate);
        $decimal = $column->decimal_count > 0 ?  'D' . str_pad('9', $column->decimal_count - 1, '9') . '0' : '';
        $format_mask = 'FM' . str_pad('9', 12, '9') . $decimal;
         if (isset($aggregate->include_docs) && !is_null($aggregate->include_docs)) {
             $lquery ="select h.id, h.unit_code, h.unit_name, v.doc_id, to_char(v.value, '$format_mask') AS value from statdata v
          join documents d on d.id = v.doc_id
          join mo_hierarchy h on d.ou_id = h.id
          where v.doc_id in ({$aggregate->include_docs})
            and v.row_id = {$row->id}
            and v.col_id = {$column->id}
            and h.blocked = 0
            and v.value is not null
          order by h.unit_code";
             $result['layers'] = \DB::select($lquery);
         } else {
             $result['layers'] = [];
         }
        $pquery = "select p.name AS period, to_char(v.value, '$format_mask') AS value from statdata v
          join documents d on d.id = v.doc_id
          join periods p on p.id = d.period_id
          where d.ou_id = {$document->ou_id}
            and d.form_id = {$document->form_id}
            and v.row_id = {$row->id}
            and v.col_id = {$column->id}
            and v.value is not null
          order by p.name";
        $result['periods'] = \DB::select($pquery);
        return $result;
    }
}
