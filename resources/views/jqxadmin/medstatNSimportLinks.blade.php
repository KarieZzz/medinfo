@extends('jqxadmin.app')

@section('title', 'Загрузка данных из формата Медстат')
@section('headertitle', 'Менеджер загрузки данных из формата Медстат (ЦНИИОИЗ)')

@section('content')
    <div class="col-sm-offset-1 col-sm-7">
        <h3>Импорт данных по соответствию структуры формата Медстат (Новосибирск) и формата Медстат (ЦНИИОИЗ)</h3>
        <form action="/admin/sctruct/medstatimport" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <input type="file"  name="medstat_ns_links" class="form-control input-lg" id="medstat_ns_links">
            </div>
            <button type="submit" class="btn btn-primary">Загрузить</button>
        </form>
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
