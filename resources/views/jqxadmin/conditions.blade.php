@extends('jqxadmin.app')

@section('title', 'Условия закрещивания ячеек')
@section('headertitle', 'Редактирование/ввод условия закрещивания ячеек')

@section('content')
<div id="mainSplitter" >
    <div>
        <div id="conditionList" style="margin: 10px"></div>
    </div>
    <div id="formContainer">
        <div id="periodPropertiesForm" class="panel panel-default">
            <div class="panel-heading"><h3>Редактирование/ввод условия закрещивания ячеек</h3></div>
            <div class="panel-body">
                <form id="period" class="form-horizontal" >
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="condition_name">Наименование условия:</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" id="condition_name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="group_id">Группа учреждений:</label>
                        <div class="col-sm-2">
                            <div id="group_id" style="padding-left: 12px"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="aggregate"></label>
                        <div class="col-sm-2">
                            <div id="exclude"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-7">
                            <button type="button" id="save" class="btn btn-success">Сохранить изменения</button>
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
    <script src="{{ asset('/jqwidgets/jqxdatatable.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxtreegrid.js') }}"></script>
    <script src="{{ asset('/jqwidgets/localization.js') }}"></script>
    <script src="{{ asset('/medinfo/admin/conditionadmin.js') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        var conditionDataAdapter;
        var groups = {!! $groups !!}
        initsplitter();
        initdatasources();
        initdropdowns();
        initConditionList();
        initformactions();
    </script>
@endsection
