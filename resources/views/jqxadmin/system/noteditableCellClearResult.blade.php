@extends('jqxadmin.app')

@section('title')
    <h3>Очистка данных из закрещенных ячеек</h3>
@endsection
@section('headertitle', 'Очистка данных из закрещенных ячеек')

@section('content')
    @yield('title')
    @include('jqxadmin.error_alert')
    <div class="col-sm-offset-1 col-sm-9">
        <div class="panel panel-default" style="height:100%; overflow: auto; padding: 20px">
            <h4>Операция завершена. Очишено {{ $cleared }} заполненных закрещенных ячеек</h4>
        </div>
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript"></script>
@endsection