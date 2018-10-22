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
            <div class="form-group">
                <button type="button" id="selectedcheck" class="btn btn-warning" title="Выборочный контроль"> >> </button>
            </div>
        </form>
@endsection

@push('loadjsscripts')
    <script src="{{ asset('/medinfo/admin/cfunctionadminall.js?v=018') }}"></script>
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
