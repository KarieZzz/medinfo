@extends('jqxadmin.app')

@section('title', 'Загрузка данных из формата Медстат')
@section('headertitle', 'Менеджер загрузки данных из формата Медстат (ЦНИИОИЗ)')

@section('content')
    @include('jqxadmin.error_alert')
    <div class="col-sm-offset-1 col-sm-7">
        <h3>Импорт контролей из формата Медстат (Новосибирск)</h3>
        <form action="/admin/cfunctions/medstatnskimport" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <input type="file"  name="medstat_nsk_controls" class="form-control input-lg" id="medstat_nsk_controls">
            </div>
            <div class="form-group">
                <div class="checkbox">
                    <label><input type="checkbox" name="skip_upload" id="skip_upload" value="1">Использовать ранее загруженные данные</label>
                </div>
            </div>
            <p class="text-info">Файл для импорта данных из формата Медстат (НСК) должен быть архивом ZIP, в который помещен файл
                <code>Int_control.csv</code> экспортированный из базы данных <code>mdsmain</code>. Разделитель полей БД <code>,</code>. Десятичный разделитель <code>.</code> .
            </p>
            <p class="text-info">Внимание! Если ранее не производилось сопоставление структур Мединфо и Медстат (НСК), необходимо загрузить структуру через
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
