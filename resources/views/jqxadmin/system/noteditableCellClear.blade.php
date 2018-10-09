@extends('jqxadmin.app')

@section('title')
    <h3>Очистка данных из закрещенных ячеек</h3>
@endsection
@section('headertitle', 'Очистка данных из закрещенных ячеек')

@section('content')
    @yield('title')
    @include('jqxadmin.error_alert')
    <div class="col-sm-offset-1 col-sm-9">
        <div class="panel panel-default" style="max-height:850px; overflow: auto; padding: 20px">
            <h4>В системе обнаружено {{ $dirty_cells }} заполненных закрещенных ячеек</h4>
            <h4>Выберите отчетные период, альбом форм, формы, где будут очищены данные</h4>
            <form style="margin-top: 3px" action="/admin/system/clearnecells" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="album">Альбом форм:</label>
                    <select class="form-control" name="album" id="album">
                        <option></option>
                        @foreach($albums as $album)
                            <option value="{{$album->id}}">{{$album->album_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="period">Период:</label>
                    <select class="form-control" name="period" id="period">
                        <option></option>
                        @foreach($periods as $period)
                            <option value="{{$period->id}}">{{$period->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="selectForm">Загружать данные из выбранных форм</label>
                    <div id="selectForm"></div>
                </div>
                <div class="form-group">
                    <button type="button" id="checkAllForm" class="btn btn-default btn-sm">Выбрать все формы</button>
                    <button type="button" id="uncheckAllForm" class="btn btn-default btn-sm">Очистить</button>
                </div>
                <input id="formids" name="formids" type="hidden" value="">
                <input id="selectedallforms" name="selectedallforms" type="hidden" value="">
                <button type="submit" class="btn btn-primary">Очистить данные</button>
            </form>
            <div class="row" style="margin-top: 20px">
                <div class="col-md-offset-1 col-md-10">
                    <p class="text text-danger ">Данные будут удалены без возможности восстановления! Рекомендуется провести предварительное резервное копирование.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('loadjsscripts')
    <script src="{{ asset('/medinfo/admin/medstatnskstructinport.js?v=004') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let forms = {!! $forms  !!};
        let allforms = $("#selectedallforms");
        let sel = $("#selectForm");
        let formsids = $("#formids");
        initdatasources();
        initcontrols();
    </script>
@endsection