@extends('jqxadmin.app')

@section('title', 'Выборочный контроль данных')
@section('headertitle', 'Выборочный контроль данных')

@section('content')
    @include('jqxadmin.error_alert')

        <div id="propertiesForm" class="panel panel-default" style="padding: 3px; width: 80%">
            <form id="form" class="form-horizontal" >
                <div class="form-group">
                    <label class="control-label col-sm-3" for="script">Функция контроля:</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="script" value="{{ $cf->script }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">
                        <p class="text-info"><strong>Форма: </strong>{{ $form->form_code }};
                        <strong>Таблица: </strong>({{ $table->table_code }}) {{ $table->table_name }} </p>
                    </div>
                </div>
                <div class="form-group">
                    <label  class="control-label col-sm-3" for="monitoring">Мониторинг:</label>
                    <div class="col-sm-8">
                        <select class="form-control" name="monitoring" id="monitoring">
                            <option></option>
                            @foreach($monitorings as $monitoring)
                                <option value="{{$monitoring->id}}">{{$monitoring->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3" for="period">Период:</label>
                    <div class="col-sm-8">
                        <select class="form-control" name="period" id="period">
                            <option></option>
                            @foreach($periods as $period)
                                <option value="{{$period->id}}">{{$period->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3" for="level">Ограничение по территории/группе:</label>
                    <div class="col-sm-8">
                        <div id="levelListContainer"><div id="levelList"></div></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-1 col-sm-7">
                        <button type="button" id="performControl" class="btn btn-primary">Выполнить контроль</button>
                        <button type="button" id="hideValid" class="btn btn-default">Показать только с ошибками</button>
                    </div>
                </div>
            </form>
            <div id="checkingProgress" style="display: none">
                <div class="row">
                    <div class="col-md-offset-1 col-sm-10">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                                 data-keyboard="false" style="width:0" id="progress">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-offset-1 col-sm-2">
                        <p id="ou_intro" class="text-right">Обработано:</p>
                    </div>
                    <div class="col-sm-9">
                        <p id="count"></p>
                    </div>
                </div>
            </div>
        </div>
        <div id="ptitle" class="row" style="display: none">
            <div class="col-md-12"><h3>Протокол контроля</h3></div></div>
        <div class="row">
            <div id="protocol" class="col-md-12"></div>
        </div>
@endsection

@push('loadjsscripts')
    <script src="{{ asset('/medinfo/admin/selectedcontrol.js?v=031') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let output_url = '/admin/dcheck/selected/perform';
        let progress_url = '/admin/dcheck/selected/getprogress';
        let form = {{ $form->id }};
        let table = {{ $table->id }};
        let levels = {!! $upper_levels  !!};
        let llc = $("#levelListContainer");
        let levellist = $("#levelList");
        let current_level = 0;
        let current_type = 1; // по территории - 1, по группе - 2
        initdatasources();
        initFilters();
        initActions();
    </script>
@endsection
