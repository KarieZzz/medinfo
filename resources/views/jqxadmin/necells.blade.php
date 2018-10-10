@extends('jqxadmin.app')

@section('title', 'Нередактируемые ячейки')
@section('headertitle', 'Менеджер нередактируемых ячеек')

@section('content')
    @include('jqxadmin.table_picker')
<div id="mainSplitter" >
    <div>
        <div id="tableGrid" style="margin: 10px"></div>
    </div>
    <div>
        <div id="columnPropertiesForm" class="panel panel-default" style="padding: 3px; width: 100%">
            {{--<div class="panel-heading"><h3>Cвойства нередактируемых ячеек</h3></div>--}}
            <div class="panel-body">
                <form id="columnform" class="form-horizontal" >
                    {{--<div class="form-group">
                        <label class="control-label col-sm-3" for="condition">Условие закрешивания:</label>
                        <div class="col-sm-2">
                            <div id="condition" style="padding-left: 12px"></div>
                        </div>
                    </div>--}}
                    <div class="form-group">
                        <div class="col-sm-offset-1 col-sm-11">
                            <button type="button" id="editable" class="btn btn-success">Разрешить редактирование</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-1 col-sm-11">
                            <button type="button" id="noteditable" class="btn btn-danger">Запретить редактирование</button>
                        </div>
                    </div>
                </form>
                {{--<div class="well" id="selectedInfo">Выделено ячеек: 0</div>--}}
                {{--<div class="well" id="conditionInfo">Условия не определены</div>--}}
            </div>
        </div>
    </div>
</div>
@endsection

@push('loadjsscripts')
{{--    <script src="{{ asset('/jqwidgets/jqxsplitter.js') }}"></script>
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
    <script src="{{ asset('/jqwidgets/jqxgrid.edit.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdatatable.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxtreegrid.js') }}"></script>
    <script src="{{ asset('/jqwidgets/localization.js') }}"></script>--}}
    <script src="{{ asset('/medinfo/admin/tablepicker.js?v=008') }}"></script>
    <script src="{{ asset('/medinfo/admin/necellsadmin.js?v=008') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let tableDataAdapter;
        let formsDataAdapter;
        let rowsDataAdapter;
        let dataAdapter;
        let tablesource;
        let gridsource;
        let columnsource;
        let sellselectbegin;
        let grid = $("#tableGrid");
        let gridfetch_url = '/admin/necells/grid/';
        let cellsfetch_url = '/admin/necells/fetchnecells/';
        let fetchcellcondition_url = '/admin/necells/fetchcellcondition/';
        let changecellstate_url = '/admin/necells/changecellstate/';
        let changerangestate_url = '/admin/necells/range/';
        let forms = {!! $forms  !!};
        //let conditions = {!! $conditions !!};
        let conditions = 0;
        let current_form = 0;
        let current_table = 0;
        let cellbeginedit = null;
        let datafields = [ { name: 'id' }, { name: '1' } ];
        let columns = [{ text: '1', columngroup: 'графа1', datafield: '1', width: 250 } ];
        let columngroups = [ { text: 'Графа 1', align: 'center', name: 'графа1' } ] ;
        let rows;
        initFilterDatasources();
        initsplitter();
        initdatasources();
        //initdropdowns();
        initFormTableFilter();
        initTableGrid();
        initCellActions();
    </script>
@endsection
