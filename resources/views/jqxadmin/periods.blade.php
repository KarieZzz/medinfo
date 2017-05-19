@extends('jqxadmin.app')

@section('title', 'Отчетные периоды')
@section('headertitle', 'Менеджер отчетных периодов')

@section('content')
<div id="mainSplitter" >
    <div>
        <div id="periodList" style="margin: 10px"></div>
    </div>
    <div id="formContainer">
        <div id="periodPropertiesForm" class="panel panel-default" style="padding-bottom: 3px; width: 90%">
            <div class="panel-heading"><h3>Редактирование/удаление отчетного периода</h3></div>
            <div class="panel-body">
                <form id="period" class="form-horizontal" >
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="name">Наименование периода:</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" id="name" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="begin_date">Начало периода:</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" id="begin_date" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="end_date">Окончание периода:</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" id="end_date" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="medinfo_id">Мединфо Id:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="medinfo_id">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-7">
                            <button type="button" id="save" class="btn btn-default">Сохранить изменения</button>
                            {{--<button type="button" id="insert" class="btn btn-default">Вставить новую запись</button>--}}
                            <button type="button" id="delete" class="btn btn-danger">Удалить запись</button>
                            <button type="button" id="create" class="btn btn-success">Создать</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div id="createPeriodForm">
    <div>Заполните параметры нового периода</div>
    <div style="padding: 15px;">
        <form id="periodcreate" class="form-horizontal" >
            <div class="form-group">
                <label class="control-label col-sm-3" for="name">Наименование (только для произвольных периодов):</label>
                <div class="col-sm-7">
                    <textarea class="form-control" rows="2" id="name" disabled></textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3" for="year">Выберите год отчетного периода :</label>
                <div class="col-sm-7">
                    <select class="form-control" id="year">
                    @foreach ($years as $year)
                        <option>{{ $year }}</option>
                    @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3" for="pattern_id">Шаблон:</label>
                <div class="col-sm-8">
                    <div id="pattern_id" style="padding-left: 12px"></div>
                </div>
            </div>
        </form>
        <div>
            <div style="float: right; margin-top: 15px;">
                <button type="button" id="ok" class="btn btn-default">Сохранить</button>
                <button type="button" id="cancel" class="btn btn-default">Отменить</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('loadjsscripts')
    <script src="{{ asset('/jqwidgets/jqxsplitter.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdata.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxpanel.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxscrollbar.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxinput.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxbuttons.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdropdownbutton.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxcheckbox.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxlistbox.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdropdownlist.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.filter.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.columnsresize.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.selection.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdatatable.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxwindow.js') }}"></script>
    <script src="{{ asset('/jqwidgets/localization.js') }}"></script>
    <script src="{{ asset('/medinfo/admin/periodadmin.js?v=005') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        var patterns = {!! $period_patterns !!};
        var periodDataAdapter;
        var patternDataAdapter;
        var plist = $('#periodList');
        initsplitter();
        initdatasources();
        initperiodlist();
        initFormElements();
        initCreatePeriodWindow();
        initCreateActions();
        initformactions();
    </script>
@endsection
