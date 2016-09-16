@extends('jqxadmin.app')

@section('title', '<h2>Администрирование Мединфо</h2>')

@section('content')
    <div class="col-sm-offset-1 col-sm-7">
        <h4>Выберите раздел из меню</h4>
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
