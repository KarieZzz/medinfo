@extends('jqxadmin.app')

@section('title', 'Загрузка данных из формата Медстат (Новосибирск)')
@section('headertitle', 'Менеджер загрузки данных из формата Медстат (Новосибирск)')

@section('content')
    @include('jqxadmin.error_alert')
    <div class="col-sm-offset-1 col-sm-9">
        <div class="panel panel-default" style="max-height:850px; overflow: auto; padding: 20px">
            <h4>Данные из предоставленного файла получены успешно</h4>
            <p>В систему загружено {{ $rec_count }} значений</p>
            <p>Имеются данные по следующим формам:</p>
            <ol>
                @foreach($forms_available as $f)
                    <li>({{ $f->form_code }}) {{ $f->form_name }}</li>
                @endforeach
            </ol>
            <h4>Для последующей загрузки необходимо сопоставить отчетные периоды и учреждения/территории куда будут перенесены данные</h4>
            <form style="margin-top: 3px" action="/admin/documents/medstatnskimportmake" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="monitoring">Мониторинг:</label>
                    <select class="form-control" name="monitoring" id="monitoring">
                        <option></option>
                        @foreach($monitorings as $monitoring)
                            <option value="{{$monitoring->id}}">{{$monitoring->name }}</option>
                        @endforeach
                    </select>
                </div>
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
                    <label for="state">Исходный статус документов:</label>
                    <select class="form-control" name="state" id="state">
                        <option></option>
                        @foreach($states as $state)
                            <option value="{{$state->code}}">{{$state->name }}</option>
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
                <p class="text text-info">Будут добавлены первичные документы.
                    Если в системе уже имеются документы той же организации, за тот же отчетный период, то будут изменены дополнительные параметры
                    (мониторинг, альбом форм, исходный статус), на указанные Вами при импорте данных.
                </p>
                <input id="formids" name="formids" type="hidden" value="">
                <input id="selectedallforms" name="selectedallforms" type="hidden" value="">
                <button type="submit" class="btn btn-primary">Загрузить</button>
            </form>
            <h4 class="text text-danger ">Внимание! Уже имеющиеся в системе данные по совпадающим формам будут перезаписаны!</h4>
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