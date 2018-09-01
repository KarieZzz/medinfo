@extends('jqxadmin.app')

@section('title', 'Правила рассчета консолидированных форм')
@section('headertitle', 'Правила рассчета консолидированных форм')

@section('content')
    @include('jqxadmin.table_picker')
    <form style="margin-top: 3px" >
        <div class="form-group row">
            <label class="sr-only"  for="Rule">Правило расчета:</label>
            <div class="col-sm-8">
                <input type="text" class="form-control mb-2 mr-sm-2 mb-sm-0" id="Rule" placeholder="Правило расчета">
            </div>
            <div class="col-sm-4">
                <button id="applyrule" type="button" class="btn btn-primary">Применить</button>
                <button id="clearrule" type="button" class="btn btn-danger">Очистить</button>
            </div>
        </div>
        <div class="form-group row">
            <label class="sr-only"  for="List">Списки МО:</label>
            <div class="col-sm-8">
                <input type="text" class="form-control mb-2 mr-sm-2 mb-sm-0" id="List" placeholder="Списки медицинских организаций">
            </div>
            <div class="col-sm-4">
                <button id="applylist" type="button" class="btn btn-primary">Применить</button>
                <button id="clearlist" type="button" class="btn btn-danger">Очистить</button>
            </div>
        </div>
    </form>
    <div class="row" style="height: 50px">
        <div class="col-lg-12" id="Selection"></div>
    </div>
    <div class="row">
        <div class="col-lg-12"><div id="Grid"></div></div>
    </div>
@endsection

@push('loadjsscripts')
{{--    <script src="{{ asset('/jqwidgets/jqxdata.js') }}"></script>
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
    <script src="{{ asset('/jqwidgets/localization.js') }}"></script>--}}
    <script src="{{ asset('/medinfo/admin/consrulesandlists.js?v=012') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let grid = $("#Grid");
        let ruleinput = $("#Rule");
        let listinput = $("#List");
        let selectionlog = $("#Selection");
        let current_row_name_datafield;
        let current_row_number_datafield;
        let selected = [];
        let rules_url = '/admin/consolidation';
        let getscripts_url = '/admin/cons';
        let applyrule_url = '/admin/cons/applyrule';
        let applylist_url = '/admin/cons/applylist';
        let fetchlists_url = '/admin/units/fetchlists_w_reserved';
        let cellbeginedit = null;
        gridEventsInit();
        initactions();
    </script>
    @include('jqxadmin.table_pickerjs')
@endsection
