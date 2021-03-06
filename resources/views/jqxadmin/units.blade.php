@extends('jqxadmin.app')

@section('title', 'Территории/медицинские организации')
@section('headertitle', 'Менеджер организационных единиц')

@section('content')
<div id="mainSplitter" >
    <div>
        <div id="unitList"></div>
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
{{--                    <div class="form-group">
                        <label class="control-label col-sm-3" for="unit_code">Тип территории (1-город, 2-район, 3-район округа):</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="territory_type">
                        </div>
                    </div>--}}
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
                        <label class="control-label col-sm-3" for="adress">Адрес:</label>
                        <div class="col-sm-8">
                            <textarea rows="3" class="form-control" id="adress"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="report">Первичные отчеты:</label>
                        <div class="col-sm-8">
                            <div id="report"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="aggregate">Сводные отчеты:</label>
                        <div class="col-sm-8">
                            <div id="aggregate"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="blocked">Блокирована:</label>
                        <div class="col-sm-8">
                            <div id="blocked"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="countryside">Сельская местность:</label>
                        <div class="col-sm-8">
                            <div id="countryside"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-1 col-sm-8">
                            <button type="button" id="save" class="btn btn-primary">Сохранить изменения</button>
                            <button type="button" id="insert" class="btn btn-success">Вставить новую запись</button>
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
    <script src="{{ asset('/medinfo/admin/unitadmin.js?v=009') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let rowsDataAdapter;
        let tableDataAdapter;
        let unittypesDataAdapter;
        let aggregatableDataAdapter;
        let unitTypes = {!! $unit_types !!};
        let aggregatables = {!! $aggregate_units !!};
        let unitlist = $("#unitList");
        let unitfetch_url ='/admin/units/fetchunits';
        let unitcreate_url ='/admin/units/create';
        let unitupdate_url ='/admin/units/update/';
        let unitdelete_url ='/admin/units/delete/';
        initdropdowns();
        initsplitter();
        initdatasources();
        inittablelist();
        initunitactions();
    </script>
@endsection
