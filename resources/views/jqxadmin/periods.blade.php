@extends('jqxadmin.app')

@section('title', '<h2>Администрирование: отчетные периоды</h2>')
@section('headertitle', 'Менеджер отчетных периодов')

@section('content')
<div id="mainSplitter" >
    <div>
        <div id="periodList" style="margin: 10px"></div>
    </div>
    <div id="formContainer">
        <div id="periodPropertiesForm" class="panel panel-default" style="padding-bottom: 3px; width: 90%">
            <div class="panel-heading"><h3>Редактирование/ввод отчетного периода</h3></div>
            <div class="panel-body">
                <form id="period" class="form-horizontal" >
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="name">Наименование периода:</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" id="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="begin_date">Начало периода:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="begin_date">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="end_date">Окончание периода:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="end_date">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="pattern_id">Паттерн:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="pattern_id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="medinfo_id">Мединфо Id:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="medinfo_id">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-6">
                            <button type="button" id="save" class="btn btn-default">Сохранить изменения</button>
                            <button type="button" id="insert" class="btn btn-default">Вставить новую запись</button>
                            <button type="button" id="delete" class="btn btn-default">Удалить запись</button>
                        </div>
                    </div>
                </form>
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
    <script src="{{ asset('/jqwidgets/jqxtreegrid.js') }}"></script>
    <script src="{{ asset('/jqwidgets/localization.js') }}"></script>
    <script src="{{ asset('/medinfo/admin/periodadmin.js') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        var perioddataAdapter;
        initsplitter();
        initdatasources();
        initperiodlist();
        initformactions();
    </script>
@endsection
