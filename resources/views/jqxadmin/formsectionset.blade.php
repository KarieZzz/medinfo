@extends('jqxadmin.app')

@section('title')
    <h3>Состав раздела "{{ $formsection->section_name }}" формы {{ $formsection->form->form_code }}</h3>
@endsection

@section('headertitle', 'Менеджер раздела отчетной формы')

@section('content')
    @yield('title')
    <div id="tableList" style="margin-bottom: 10px"></div>
        <form id="formsection" class="form">
            <div class="form-group">
                 <button type="button" id="update" class="btn btn-primary">Сохранить изменения</button>
            </div>
            <div class="row">
                <div class="col-md-10"> Выбрано таблиц: <span id="count_of_included">{{ count($included_tables) }}</span></div>
            </div>
        </form>
@endsection

@push('loadjsscripts')
    <script src="{{ asset('/medinfo/admin/formsectionset.js?v=005') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let tablesDataAdapter;
        let editsection_url = '/admin/formsections/editsection/{{ $formsection->id }}';
        let tgrid = $("#tableList");
        let included_tables = {!! $included_tables  !!};
        let tables = {!! $tables !!};
        initdatasources();
        initTableList();
        initActions();
    </script>
@endsection
