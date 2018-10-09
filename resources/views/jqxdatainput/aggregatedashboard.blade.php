@extends('jqxdatainput.dashboardlayout')

@section('title')
    <h5>
        Форма №<span class="text-info">{{ $form->form_code  }} </span>
        <i class="fa fa-hospital-o fa-lg"></i> <span class="text-info">{{ $current_unit->unit_name ? $current_unit->unit_name : $current_unit->group_name}} </span>
        <i class="fa fa-calendar-o fa-lg"></i> <span class="text-info">{{ $period->name }} </span>
        <i class="fa fa-edit fa-lg"></i> <span class="text-info">{{ $editmode }} </span>
    </h5>
@endsection

@section('rp-open')
    <span class="text-right pull-right" id="rp-open" title="Открыть боковую панель"><i style="font-size: 1.5em" class="fa fa-align-justify"></i></span>
@endsection

@section('headertitle', 'Просмотр/редактирование сводного отчетного документа')

@section('additionalTabLi')
    <li>Разрез по ячейке</li>
    <li>Разрез по периодам</li>
@endsection

@section('additionalTabDiv')
    <div>
        <div style="width: 100%; overflow-y: auto" id="CellAnalysisTable"></div>
    </div>
    <div>
        <div id="CellPeriodsTable"></div>
    </div>
@endsection

@section('content')
    @include('jqxdatainput.formeditsplitter')
@endsection

@push('loadcss')
<link href="{{ asset('/css/medinfoeditform.css?v=013') }}" rel="stylesheet" type="text/css" />
@endpush('loadcss')

@push('loadjsscripts')
    <script src="{{ asset('/medinfo/editdashboard.js?v=135') }}"></script>
@endpush('loadjsscripts')

@section('inlinejs')
    @parent
    @include('jqxdatainput.dashboardjs')
@endsection