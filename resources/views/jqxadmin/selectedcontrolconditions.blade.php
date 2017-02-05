@extends('jqxadmin.app')

@section('title', 'Выборочный контроль данных')
@section('headertitle', 'Выборочный контроль данных')

@section('content')
    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>Ошибка!</strong> Не все необходимые поля заполнены.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @include('jqxadmin.table_picker')
    <div>
        <div id="propertiesForm" class="panel panel-default" style="padding: 3px; width: 100%">
            <div id="functionList" style="margin: 10px"></div>
            <form id="form" class="form-horizontal" >
                {{--<div class="form-group">
                    <label class="control-label col-sm-3" for="level">Типы документов:</label>
                    <div class="col-sm-2">
                        <div id="level" style="padding-left: 12px"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3" for="comment">Ограничение по территории:</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="comment">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3" for="blocked">Ограничение по группе:</label>
                    <div class="col-sm-2">
                        <div id="blocked"></div>
                    </div>
                </div>--}}
                <div class="form-group">
                    <div class="col-sm-offset-1 col-sm-7">
                        <button type="button" id="performControl" class="btn btn-primary">Выполнить контроль</button>
                    </div>
                </div>
            </form>
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
    <script src="{{ asset('/medinfo/admin/selectedcontrol.js?v=004') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        var formsDataAdapter;
        var functionsDataAdapter;
        var tablesource;
        var rowsource;
        var columnsource;
        var functionfetch_url = '/admin/cfunctions/fetchofform/';
        var output_url = '/admin/dcheck/selectedcheck';
        var forms = {!! $forms  !!};
        var fgrid = $("#functionList");
        var current_form = 0;
        var cfselected = [];
        initdatasources();
        initFunctionList();
        initFormFilter();
        initAction();
    </script>
@endsection
