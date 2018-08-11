@extends('jqxadmin.app')

@section('title', 'Загрузка данных из формата Медстат (Новосибирск)')
@section('headertitle', 'Менеджер загрузки данных из формата Медстат (Новосибирск)')

@section('content')
    <div class="col-sm-offset-1 col-sm-7">
        <h4>Данные из предоставленного файла получены успешно</h4>
        <p>В систему загружено {{$numrecords}} значений</p>
        <h4>Для последующей загрузки необходимо сопосставить отчетные периоды и учреждения/территории куда будут перенесены данные</h4>
        <form style="margin-top: 3px" action="/admin/documents/medstatimportmake" method="post" enctype="multipart/form-data">
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
            <p class="text text-info">Будут добавлены первичные документы.
                Если в системе уже имеются документы той же организации, за тот же отчетный период, то будут изменены дополнительные параметры
                (мониторинг, альбом форм, исходный статус), на указанные Вами при импорте данных.
            </p>
            <button type="submit" class="btn btn-primary">Загрузить</button>
        </form>
        <h4 class="text text-danger ">Внимание! Уже имеющиеся в системе данные по совпадающим формам будут перезаписаны!</h4>
    </div>

@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
