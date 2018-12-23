@extends('jqxadmin.app')

@section('title', 'Правила рассчета консолидированных форм')
@section('headertitle', 'Правила рассчета консолидированных форм')

@section('content')
    @include('jqxadmin.table_picker')
    <form style="margin-top: 3px" >
        <div class="form-group row">
            <label class="sr-only"  for="Rule">Правило расчета:</label>
            <div class="col-md-8">
                <input type="text" class="form-control mb-2 mr-sm-2 mb-sm-0" id="Rule" spellcheck="false" placeholder="Правило расчета">
            </div>
            <div class="col-md-2">
                <button id="applyrule" type="button" class="btn btn-primary">Применить</button>
                <button id="clearrule" type="button" class="btn btn-danger">Очистить</button>
            </div>
        </div>
        <div class="form-group row">
            <label class="sr-only"  for="List">Списки МО:</label>
            <div class="col-md-8">
                <input type="text" class="form-control mb-2 mr-sm-2 mb-sm-0" id="List" spellcheck="false" placeholder="Списки медицинских организаций">
            </div>
            <div class="col-md-2">
                <button id="applylist" type="button" class="btn btn-primary">Применить</button>
                <button id="clearlist" type="button" class="btn btn-danger">Очистить</button>
            </div>
            <div class="col-md-2">
                <div id="Selection"></div>
            </div>
        </div>
    </form>
    <div class="row" style="margin:0;padding:0">
        <div class="col-md-12" style="margin: 0;padding: 0;"><div id="Grid"></div></div>
    </div>
@endsection

@push('loadjsscripts')
    <script src="{{ asset('/medinfo/admin/consrulesandlists.js?v=014') }}"></script>
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
