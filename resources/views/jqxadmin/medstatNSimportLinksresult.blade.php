@extends('jqxadmin.app')

@section('title', 'Загрузка данных из формата Медстат')
@section('headertitle', 'Менеджер загрузки данных из формата Медстат (ЦНИИОИЗ)')

@section('content')
    <div class="col-sm-offset-1 col-sm-9">
        <div class="panel panel-default" style="max-height:850px; overflow: auto; padding: 20px">
            <div class="page-header">
                <h3>Результат загрузки структуры отчетных форм из формата Медстат (Новосибирск)</h3>
            </div>
            <div class="panel-body">
                <p class="text text-info">Загрузка завершена.</p>
                <p class="text text-info">Загружено записей: .</p>
                <ul>
                    <li>Формы: {{ $form_count }}</li>
                    <li>Таблицы: {{ $table_count }} (транспонированные: {{ $tansposed_nsktables }})</li>
                    <li>Строки: {{ $row_count }}</li>
                    <li>Графы: {{ $column_count }}</li>
                </ul>
                <p class="text text-info">Сопоставлено кодов: .</p>
                <ul>
                    <li>Формы: {{ $matched_forms }}</li>
                    <li>Таблицы: {{ $matched_tables }}</li>
                    <li>Строки: {{ $matched_rows }}</li>
                    <li>Графы: {{ $matched_columns }}</li>
                </ul>
                <h4>Выявлены следующие несоотствия структуры:</h4>
                <p>Не сопоставлены формы (отсутствуют в Медстат (НСК)):</p>
                <ol>
                    @foreach ($form_disparity as $fd)
                        <li>({{ $fd['form_code'] }}) {{ $fd['form_name'] }}</li>
                    @endforeach
                </ol>
                <p>Не сопоставлены таблицы (отсутствуют в Медстат (НСК)):</p>
                <ol>
                    @foreach ($table_disparity as $td)
                        <li>({{ $td['form']['form_code'] }}) {{ $td['table_code'] }} {{ $td['table_name'] }}</li>
                    @endforeach
                </ol>
                <p>Несопоставлено транспонирование таблиц:</p>
                <ol>
                    @foreach ($transposed_disparity as $td)
                        <li>({{ $td['form_code']  }}){{ $td['table_code'] }} <span class="text text-danger">{{ $td['comment'] }}</span></li>
                    @endforeach
                </ol>
                <p>Несоотвествие по составу строк:</p>
            </div>
        </div>
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
