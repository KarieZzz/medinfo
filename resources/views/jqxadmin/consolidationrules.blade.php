@extends('jqxadmin.app')

@section('title', 'Правила рассчета консолидированных форм')
@section('headertitle', 'Правила рассчета консолидированных форм')

@section('content')
    @include('jqxadmin.table_picker')
        <form style="margin-top: 3px" >
            <div class="form-group row">
                <label class="sr-only"  for="rule">Правило рассчета:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control mb-2 mr-sm-2 mb-sm-0" id="rule" placeholder="Правило рассчета">
                </div>
                <div class="col-sm-3">
                    <button type="button" class="btn btn-default">Сохранить</button>
                    <button type="button" class="btn btn-default">Отменить</button>
                </div>
            </div>

        </form>
    <div class="row">
        <div class="col-lg-12"><div id="Grid"></div></div>
    </div>
    <div>

    </div>

@endsection

@push('loadjsscripts')
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
    <script src="{{ asset('/jqwidgets/localization.js') }}"></script>
    {{--<script src="{{ asset('/medinfo/admin/tablepicker.js?v=008') }}"></script>--}}
    <script src="{{ asset('/medinfo/admin/consolidationrules.js?v=002') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let grid = $("#Grid");
    </script>
    @include('jqxadmin.table_pickerjs')
@endsection
