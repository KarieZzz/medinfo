<?php

namespace App\Http\Controllers\ImportExport;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Document;
use App\Album;
use App\Form;
use App\Table;
use App\Row;
use App\Column;
use App\Cell;
use Storage;
use Carbon\Carbon;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use App\Medinfo\TableEditing;

class ImportDataFromExcelController extends Controller
{
    //
    public $eo; // Объект эксель
    public $document;
    public $form;
    public $realform;
    public $album;

    public function __construct()
    {
        $this->middleware('datainputauth');
    }

    public function importData(Document $document, Table $table, Request $request)
    {
        $worker = Auth::guard('datainput')->user();
        if (!TableEditing::isEditPermission($worker->permission, $document->state)) {
            return response(['error' => 'Отсутствуют права для изменения данных в этом документе (по статусу документа)'])->header('Content-Type', 'text/html');
        }
        $this->document = $document;
        $this->album = Album::find($document->album_id);
        $this->form = Form::find($document->form_id);
        $this->realform = Form::getRealForm($document->form_id);

        $excel_file = 'import_' . str_random(8) . '.xlsx';
        \Storage::put(
            'imports/data/excel/' . $excel_file,
            file_get_contents($request->file('fileToUpload')->getRealPath())
        );
        try {
            $this->eo = $this->openDataFile($excel_file);
        } catch (\Exception $e) {
            return response(['error' => $e->getMessage()])->header('Content-Type', 'text/html');
        }
        $lists = $this->eo->getAllSheets();
        $active_sheet = null;
        $result = [];
        for ($i = 0; $i < count($lists); $i++ ) {
            $title = $lists[$i]->getTitle();
            $codes = explode("_", $title);
            if ($codes[0] == $this->form->form_code) {
                if (isset($codes[1])) {
                    $t = Table::OfFormTableCode($this->realform->id, $codes[1])->first();
                    if (TableEditing::isTableBlocked($document->id, $t->id)) {
                        $result[$codes[1]] = ['saved' => 'Данные в таблице не изменены (раздел документа принят)', 'deleted' => 0];
                    } else {
                        $result[$codes[1]] = $this->getDataFromSheet($lists[$i], $t);
                    }

                } else {
                    $result[$codes[1]] = ['saved' => 'Данные в таблице не изменены (раздел документа принят)', 'deleted' => 0];
                    //throw new \Exception("Таблица с кодом {$codes[1]} не найдена в форме {$this->form->form_code}");
                }
            }
        }

        //$response = compact('saved','deleted');
        return response($result)->header('Content-Type', 'text/html');
    }

    private function openDataFile($excel_file)
    {
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $path = storage_path('app/imports/data/excel/' . $excel_file);
        if (!is_file($path)) {
            throw new \Exception('Файл для импорта данных не найден');
        }
        $excel_object = $objReader->load($path);
        return $excel_object;
    }

    private function getDataFromSheet($sheet, Table $table)
    {
        $rows = Row::OfTable($table->id)->whereDoesntHave('excluded', function ($query) {
            $query->where('album_id', $this->album->id);
        })->orderBy('row_index')->get();
        $cols = Column::OfTable($table->id)->OfDataType()->whereDoesntHave('excluded', function ($query) {
            $query->where('album_id', $this->album->id);
        })->orderBy('column_index')->get();
        $deleted = 0;
        $saved = 0;
        for ($i = 0; $i < count($rows); $i++) {
            for ($j = 0; $j < count($cols); $j++) {
                if ($sheet->getCellByColumnAndRow($j + 2,$i + 6)->isFormula()) {
                    $v = (float)$sheet->getCellByColumnAndRow($j + 2,$i + 6)->getOldCalculatedValue();
                } else {
                    $v = (float)$sheet->getCellByColumnAndRow($j + 2,$i + 6)->getValue();
                }
                if ($v === 0.0) {
                    if ($cell = Cell::ofDTRC($this->document->id, $table->id, $rows[$i]->id, $cols[$j]->id)->first()) {
                        $cell->delete();
                        $deleted++;
                    }
                } else {
                    $cell = Cell::firstOrCreate(['doc_id' => $this->document->id, 'table_id' => $table->id, 'row_id' => $rows[$i]->id, 'col_id' => $cols[$j]->id]);
                    $cell->value = $v;
                    $cell->save();
                    $saved++;
                }
                //dump($v);
            }
        }
        return compact('saved', 'deleted');
    }
}