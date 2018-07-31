@extends('jqxadmin.app')

@section('title', 'Загрузка данных из формата Медстат')
@section('headertitle', 'Менеджер загрузки данных из формата Медстат (ЦНИИОИЗ)')

@section('content')
    <div class="col-sm-offset-1 col-sm-7">
        <h3>Загрузка структуры территорий/медицинских организаций из формата Медстат (Новосибирск)</h3>
        <p class="text text-info">Загрузка завершена.</p>
        <p class="text text-info">Загружено записей: {{ $numrecords }}.</p>
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
