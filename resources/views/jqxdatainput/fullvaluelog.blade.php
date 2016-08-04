<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Журнал изменний данных по форме . Период  год.</title>
<link href="{{ asset('/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
<style>
    .control_result {
        border: 1px solid #ddd;
        border-collapse: collapse;
        margin-bottom: 10px;
    }
    .control_result td { border: 1px solid #ddd; }
    }
</style>
<body>
<div class="container">
    <h3>Протокол изменений документа № {{ $document->id }}</h3>
    <h4>Медицинская организация: {{ $current_unit->unit_name }}. Форма {{ $form->form_code  }}. Период: {{ $period->name }} </h4>
    <div id="documentLog">
        <table class="table table-bordered table-condensed">
            <tr>
                <th>Дата и время</th>
                <th>Сотрудник</th>
                <th>Таблица</th>
                <th>Строка</th>
                <th>Графа</th>
                <th>Старое значение</th>
                <th>Новое значение</th>
            </tr>
            @foreach( $values as $v )
                <tr>
                    <td>{{ $v->occured_at }}</td>
                    <td>{{ $v->worker->description }}</td>
                    <td>{{ $v->table->table_code }}</td>
                    <td>{{ $v->row->row_code }}</td>
                    <td>{{ $v->column->column_index }}</td>
                    <td>{{ $v->oldvalue }}</td>
                    <td>{{ $v->newvalue }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
<script src="{{ asset('/plugins/jquery/jquery-1.12.4.min.js') }}" type="text/javascript" ></script>
<script src="{{ asset('/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>
</body>
</html>