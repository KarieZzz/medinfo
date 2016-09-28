@extends('jqxadmin.app')

@section('title', 'Выберите раздел из меню')

@section('content')
    <div class="col-sm-offset-1 col-sm-7">
        <h4>Структура отчетов, организационные единицы, исполнители</h4>
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
