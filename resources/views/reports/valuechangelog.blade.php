@extends('reports.report_layout')

@section('content')
    <div class="row" style="margin-top: 20px">
        <h3>Журнал изменений по форме №{{ $form->form_code }}. {{ $form->form_name }} за период "{{ $period->name }}"</h3>
        <h3>Медицинская организация: {{ $unit->unit_name }}. </h3>
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
                @foreach($records as $record)
                    <tr>
                        <td>{{ $record->occured_at }}</td>
                        <td>({{ $record->worker->name }}) {{ $record->worker->description }}</td>
                        <td>{{ $record->table->table_code }}</td>
                        <td>{{ $record->row ? $record->row->row_code : 'Строка не найдена (' . $record->r . ')' }}</td>
                        <td>{{ $record->column ? $record->column->column_code : 'Графа не найдена (' . $record->c . ')' }}</td>
                        <td>{{ $record->oldvalue }}</td>
                        <td>{{ $record->newvalue }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
