@extends('jqxadmin.app')

@section('title', 'Загрузка данных из формата Медстат')
@section('headertitle', 'Менеджер загрузки данных из формата Медстат (ЦНИИОИЗ)')

@section('content')
    <div class="col-sm-offset-1 col-sm-7">
        <h3>Импорт данных из формата Медстат (Новосибирск)</h3>
        <form action="/admin/documents/medstatnskimport" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <input type="file"  name="medstat_nsk_data" class="form-control input-lg" id="medstat_nsk_data">
            </div>
            <p class="text-info">Файл для импорта данных из формата Медстат (НСК) должен быть архивом ZIP, в который помещен файл
                <code>Data</code> экспортированный в формат <code>Dbase IV (dbf)</code> из базы данных <code>data0</code>.
            </p>
            <p>Внимание! Если ранее не производилось сопоставление структур Мединфо и Медстат (НСК), необходимо загрузить структуру через
                команду меню: <code>Структура / Импорт структуры из формата Медстат (Новосибирск)</code>
            </p>
            <button type="submit" class="btn btn-primary">Загрузить</button>
        </form>
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
