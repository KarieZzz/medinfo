@extends('jqxadmin.app')

@section('title')
    <h3>Функции контроля отчетных форм (полный перечень)</h3>
@endsection

@section('headertitle', 'Менеджер функций контроля - полный перечень функций')

@section('content')
    @yield('title')
    <div id="functionList" style="margin-bottom: 10px"></div>
        <form id="form" class="form-inline">
            <div class="form-group">
                <label for="level">Уровень:</label>
                <select class="form-control" id="level">
                    <option></option>
                    @foreach($error_levels as $el)
                        <option value="{{ $el->code }}">{{ $el->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="script">Текст функции:</label>
                <input type="text" class="form-control" style="width: 450px" id="script" spellcheck="false">
            </div>
            <div class="form-group">
                <label for="comment">Комментарий:</label>
                <input type="text" class="form-control" style="width: 550px" id="comment">
            </div>
            <div class="form-group">
                <label><input type="checkbox" id="blocked" value="1">Функция отключена</label>
            </div>
            <div class="form-group">
                 <button type="button" id="save" class="btn btn-primary">Сохранить изменения</button>
            </div>
        </form>
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
    <script src="{{ asset('/jqwidgets/jqxdatatable.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxtreegrid.js') }}"></script>
    <script src="{{ asset('/jqwidgets/localization.js') }}"></script>--}}
    <script src="{{ asset('/medinfo/admin/cfunctionadminall.js?v=017') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let errorLevels = {!! $error_levels !!};
        let functionsDataAdapter;
        let functionfetch_url = '/admin/cfunctions/fetchall/';
        let fgrid = $("#functionList");
        initdatasources();
        initFunctionList();
        initFunctionActions();
    </script>
@endsection
