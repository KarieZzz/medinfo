@extends('jqxadmin.app')

@section('title', 'Загрузка данных из формата Медстат')
@section('headertitle', 'Менеджер загрузки структуры форм из формата Медстат (ЦНИИОИЗ)')

@section('content')
    <div class="col-sm-offset-1 col-sm-9">
        <div class="panel panel-default" style="max-height:850px; overflow: auto; padding: 20px">
            <div class="page-header">
                <h3>Результат загрузки структуры отчетных форм из формата Медстат (ЦНИИОИЗ)</h3>
            </div>
            <div class="panel-body">
                <p class="text text-info">Загрузка завершена.</p>
                <p class="text text-info">Загружено записей:</p>
                <ul>
                    <li>Строки: {{ $str_count }}</li>
                    <li>Графы: {{ $grf_count }}</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
