@extends('jqxadmin.app')

@section('title', 'Территории/медицинские организации')
@section('headertitle', 'Менеджер организационных единиц')

@section('content')
<div id="mainSplitter" >
    <div>
        <div id="unitList" style="margin: 10px"></div>
    </div>
    <div id="formContainer">
        <div id="propertiesForm" class="panel panel-default">
            <div class="panel-heading"><h3>Редактирование/ввод организационной единицы</h3></div>
            <div class="panel-body">
                <form id="form" class="form-horizontal" >
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="unit_name">Наименование:</label>
                        <div class="col-sm-8">
                            <textarea rows="3" class="form-control" id="unit_name"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="parent_id">Входит в состав:</label>
                        <div class="col-sm-2">
                            <div id="parent_id" style="padding-left: 12px"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="unit_code">Код территории/организации:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="unit_code">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="inn">Индивидуальный налоговый номер:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="inn">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="node_type">Тип организационной единицы:</label>
                        <div class="col-sm-2">
                            <div id="node_type" style="padding-left: 12px"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="report">Первичные отчеты:</label>
                        <div class="col-sm-2">
                            <div id="report"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="aggregate">Сводные отчеты:</label>
                        <div class="col-sm-2">
                            <div id="aggregate"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="blocked">Блокирована:</label>
                        <div class="col-sm-2">
                            <div id="blocked"></div>
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
                            <button type="button" id="insert" class="btn btn-default">Вставить новую запись</button>
                            <button type="button" id="delete" class="btn btn-danger">Удалить запись</button>
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
    <script src="{{ asset('/jqwidgets/jqxswitchbutton.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxlistbox.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdropdownlist.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.filter.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.columnsresize.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.selection.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.sort.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdatatable.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxtreegrid.js') }}"></script>
    <script src="{{ asset('/jqwidgets/localization.js') }}"></script>
    <script src="{{ asset('/medinfo/admin/unitadmin.js') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        var rowsDataAdapter;
        var tableDataAdapter;
        var unittypesDataAdapter;
        var aggregatableDataAdapter;
        var unitTypes = {!! $unit_types !!};
        var aggregatables = {!! $aggregate_units !!};
        var unitfetch_url ='/admin/units/fetchunits';
        var unitcreate_url ='/admin/units/create';
        var unitupdate_url ='/admin/units/update/';
        var unitdelete_url ='/admin/units/delete/';
        initdropdowns();
        initsplitter();
        initdatasources();
        inittablelist();
        initunitactions();
    </script>
@endsection
