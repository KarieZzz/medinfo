@extends('jqxadmin.app')

@section('title')
    <h3>Разделы отчетных форм</h3>
@endsection

@section('headertitle', 'Менеджер разделов отчетных форм')

@section('content')
    @yield('title')
    <div id="sectionList" style="margin-bottom: 10px"></div>
        <form id="formsection" class="form">
            <div class="form-group">
                <label for="form">Форма:</label>
                <select class="form-control" id="form" name="form">
                    <option></option>
                    @foreach($forms as $el)
                        <option value="{{ $el->id }}">({{ $el->form_code }}) {{ $el->form_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="section_name">Наименование раздела:</label>
                <input type="text" class="form-control" id="section_name">
            </div>
            <div class="form-group">
                 <button type="button" id="update" class="btn btn-primary">Сохранить изменения</button>
                 <button type="button" id="store" class="btn btn-success">Создать новый раздел</button>
                 <button type="button" id="delete" class="btn btn-danger">Удалить</button>
                 <button type="button" id="editSection" class="btn btn-default pull-right">Редактировать состав раздела</button>
            </div>
        </form>
@endsection

@push('loadjsscripts')
    <script src="{{ asset('/medinfo/admin/formsections.js?v=004') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let fsectionDataAdapter;
        let url = '/admin/formsections';
        let editsection_url = '/admin/formsections/editsection/';
        let fsectionfetch_url = '/admin/formsections/fetchfs';
        let fsgrid = $("#sectionList");
        initdatasources();
        initFunctionList();
        initFunctionActions();
    </script>
@endsection
