<?php

namespace App\Http\Controllers\StatDataInput;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Unit;
//use App\UnitGroupMember;
use App\UnitListMember;
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
        $aggregate = Aggregate::find($document->id); // Вызываем модель, где хранятся свойства "сводного" документа
        $protected = is_null($aggregate) ? false : $aggregate->protected; // Свойство документа "защищен". True, если является защищенным от повторного свода
        $result = []; // Массив, который будет возвращен пользователю по результатам выполнения свода
        // Если документ защищен от свода - завершаем выполнение метода aggregateData с ошибкой
        if ($protected) {
            $result['aggregate_status'] = 500;
            $result['error_message'] =  'Данный документ защищен от повторного сведения';
            return $result;
        }
        $calc = Column::Calculated()->pluck('id')->toArray(); // Массив "расчетных" граф
        $calc[] = 0; // Добавляем один элемент в массив, что бы не вызвать ошибку в sql запросе
        $calculatedColumns = implode(',', $calc); // Конвертируем массив в строку для sql запроса
        Cell::where('doc_id', $document->id)->delete(); // перед сведением - удаление старых данных (из таблицы statdata)
        // Получаем массив с id организационных единиц (ОЕ), структурно входящих в состав ОЕ, по которой делается свод
        if ($unitgroup === 1) { // вариант 1: если свод выполняется по территории/медицинской организации
            $units = Unit::getDescendants($document->ou_id);
        } elseif ($unitgroup === 2) { // вариант 2: если свод выполняется по произвольному списку (Администрирование -> Организационные единицы -> Списки медицинских организаций)
            $units = UnitListMember::List($document->ou_id)->pluck('ou_id');
        }
        // На основе списка ОЕ получаем список отчетных документов данные из которых должны попасть в свод.
        // Документы должны быть "первичными", совпадать по мониторингу, отчетной форме, отчетному периоду
        $included_documents = Document::whereIn('ou_id', $units)
            ->where('dtype', 1) // Только "первичные" документы
            ->where('monitoring_id', $document->monitoring_id) // мониторинг
            ->where('form_id', $document->form_id) // отчетная форма
            ->where('period_id', $document->period_id) // отчетный период
            ->pluck('id');
        $stringified_documents = implode(',', $included_documents->toArray()); // Конвертируем массив id документов в строку для sql - запроса
        // Если нет входящих документов - завершаем выполнение метода aggregateData с ошибкой
        if (!$stringified_documents) {
            $result['aggregate_status'] = 500;
            $result['error_message'] =  'Нет первичных документов, содержащих данные, для сведения';
            return $result;
        }
        $now = Carbon::now(); // Текущая дата и время в ISO формате для сохранения в БД
        // Собственно sql запрос, записывающий данные в таблицу statdata (ORM Модель Cell)
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
        // Запись дополнительных сведений по результат выполнения запроса (пишутся в таблицу aggregates)
        $aggregate = Aggregate::firstOrCreate(['doc_id' => $document->id]);
        // Сохраняем список id отчетных документов, вощедщих в свод, для того, что бы можно было динамически возвращать "разрез" ячейки в своде.
        $aggregate->include_docs = $stringified_documents;
        $aggregate->aggregated_at = Carbon::now();
        $aggregate->save();
        // Если все прошло успешно, возвращаем пользователю результат:
        $result['affected_cells'] = count($affected_cells); // Кол-во "затронутых" ячеек
        $result['aggregate_status'] = 200;
        $result['aggregated_at'] =  $aggregate->aggregated_at->toDateTimeString(); // Дата и время сведения
        return $result;
    }

    // Метод возвращает
    // 1: список ОЕ и данные по выбранной в своде ячейке (layers)
    // 2: данные выбранной ячейки за прошлые отчетные периоды (periods)
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
