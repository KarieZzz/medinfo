@extends('jqxadmin.app')

@section('title', 'Загрузка данных из формата Медстат')
@section('headertitle', 'Менеджер загрузки данных из формата Медстат (ЦНИИОИЗ)')

@section('content')
    <div class="col-sm-offset-1 col-sm-7">
        <h3>Загрузка структуры территорий/медицинских организаций из формата Медстат (Новосибирск)</h3>
        <form action="/admin/units/medstatimport" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <input type="file"  name="medstat_ns_units" class="form-control input-lg" id="medstat_ns_units">
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
