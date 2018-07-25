@extends('jqxadmin.app')

@section('title', 'Загрузка данных из формата Медстат')
@section('headertitle', 'Менеджер загрузки данных из формата Медстат (ЦНИИОИЗ)')

@section('content')
    <div class="col-sm-offset-1 col-sm-7">
        <h4>Данные из предоставленного файла получены успешно</h4>
        <p>В систему загружено {{$no_zero_uploaded}} не пустых значений</p>
        <p>За следующие отчетные годы:</p>
        <ul>
            @foreach($available_years as $available_year)
                <li>{{ $available_year->year }}</li>
            @endforeach
        </ul>
        <p>По следующим кодам учреждений/территорий :</p>
        <ul>
            @foreach($available_units as $available_unit)
                <li>{{ $available_unit->ucode }}</li>
            @endforeach
        </ul>
        <p>По следующим формам :</p>
        <ul>
            @foreach($available_forms as $available_form)
                <li>{{ $available_form->form }} ({!!  $available_form->medinfoform->form_code or '<span class="text text-danger">отсутствует в Мединфо</span>' !!})</li>
            @endforeach
        </ul>
        <h4>Для последующей загрузки необходимо сопосставить отчетные периоды и учреждения/территории куда будут перенесены данные</h4>
        <form style="margin-top: 3px" action="/admin/documents/medstatimportmake" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="period">Период:</label>
                <select class="form-control" name="period" id="period">
                    @foreach($periods as $period)
                        <option value="{{$period->id}}">{{$period->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="unit">Медицинская организция:</label>
                <select class="form-control" name="unit" id="unit">
                    @foreach($units as $unit)
                        <option value="{{$unit->id}}">{{$unit->unit_name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Загрузить</button>
            {{--            <div class="form-group row">
                            <label class="sr-only"  for="Period">Период:</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control mb-2 mr-sm-2 mb-sm-0" id="Period" placeholder="Период">
                            </div>
                        </div>--}}
{{--            <div class="form-group row">
                <label class="sr-only"  for="Unit">Медицинская организация:</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control mb-2 mr-sm-2 mb-sm-0" id="Unit" placeholder="Медицинская организация">
                </div>
            </div>--}}
        </form>
        <h4 class="text text-danger ">Внимание! Уже имеющиеся в системе данные по совпадающим формам будут перезаписаны!</h4>
    </div>

@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
