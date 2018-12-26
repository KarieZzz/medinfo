@extends('jqxadmin.app')

@section('title', 'Загрузка данных из формата Медстат')
@section('headertitle', 'Менеджер загрузки данных из формата Медстат (ЦНИИОИЗ)')

@section('content')
    <div class="col-sm-offset-1 col-sm-7">
        <h2>Загрузка структуры из формата Медстат (ЦНИИОиЗ)</h2>
        <form action="/admin/sctruct/ms_rows_columns_import" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <input type="file"  name="medstat_struct" class="form-control input-lg" id="medstat_struct">
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
