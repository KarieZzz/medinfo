@extends('jqxadmin.app')

@section('title', 'Загрузка данных из формата Медстат')
@section('headertitle', 'Менеджер загрузки данных из формата Медстат (ЦНИИОИЗ)')

@section('content')
    <div class="col-sm-offset-1 col-sm-7">
        <h3>Загрузка структуры отчетных форм из формата Медстат (Новосибирск)</h3>
        <p class="text text-info">Загрузка завершена.</p>
        <p class="text text-info">Загружено записей: .</p>
        <ul>
            <li>Формы: {{ $form_count }}</li>
            <li>Таблицы: {{ $table_count }}</li>
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
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
