@extends('jqxadmin.app')

@section('title', 'Загрузка данных из формата Медстат')
@section('headertitle', 'Менеджер загрузки данных из формата Медстат (ЦНИИОИЗ)')

@section('content')
    @include('jqxadmin.error_alert')
    <div class="col-sm-offset-1 col-sm-7">
        <h3>Импорт данных по соответствию структуры формата Медстат (Новосибирск) и формата Медстат (ЦНИИОИЗ)</h3>
        <form action="/admin/sctruct/medstatimport" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <input type="file"  name="medstat_ns_links" class="form-control input-lg" id="medstat_ns_links">
            </div>
            <div class="form-group">
                <label for="album">Выберите альбом форм для сопоставления структур таблиц, строк, граф:</label>
                <select class="form-control" name="album" id="album">
                    <option></option>
                    @foreach($albums as $album)
                        <option value="{{$album->id}}">{{$album->album_name }}</option>
                    @endforeach
                </select>
            </div>
            <p class="text-info">Файл для импорта структуры из формата Медстат (НСК) должен быть архивом ZIP, в который помещены файлы
                <code>Columns</code>, <code>Rows</code>, <code>Tables</code>, <code>Forms</code> из базы данных <code>mdsmain</code>. Из базы данных <code>links</code>
                в архив помещаются файлы <code>Cols</code> под именем <code>CL</code>, <code>Rows</code> под именем <code>RL</code>,
                <code>Tables</code> под именем <code>TL</code>, <code>Forms</code> под именем <code>FL</code>.
            </p>
            <p class="text-info">Все таблицы должны быть предварительно экспортированы в формат DBASE IV (dbf).</p>
            <p class="text-info">Внимание! Данные по ранее импортированной структуре будут перезаписаны! Если вносились изменения вручную, они будут потеряны!</p>
            <button type="submit" class="btn btn-primary">Загрузить</button>
        </form>
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
