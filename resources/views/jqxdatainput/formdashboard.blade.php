@extends('jqxdatainput.dashboardlayout')

@section('title')
    <h5>
        Форма №<span class="text-info">{{ $form->form_code  }} </span>
        <i class="fa fa-hospital-o fa-lg"></i> <span class="text-info">{{ $current_unit->unit_name ? $current_unit->unit_name : $current_unit->group_name}} </span>
        <i class="fa fa-map-o fa-lg"></i> <span class="text-info">{{ $monitoring->name }} </span>
        <i class="fa fa-calendar-o fa-lg"></i> <span class="text-info">{{ $period->name }} </span>
        <i class="fa fa-star fa-lg"></i> <span class="text-info">{{ $statelabel }} </span>
        <i class="fa fa-edit fa-lg"></i> <span class="text-info">{{ $editmode }} </span>
    </h5>
@endsection
@section('headertitle', 'Просмотр/редактирование первичного отчетного документа')

@section('content')
    @include('jqxdatainput.formeditlayout')
@endsection

@push('loadcss')
    <link href="{{ asset('/css/medinfoeditform.css?v=009') }}" rel="stylesheet" type="text/css" />
@endpush('loadcss')

@push('loadjsscripts')
    @include('jqxdatainput.jsstack')
    <script src="{{ asset('/medinfo/editdashboard.js?v=060') }}"></script>
    <script src="{{ asset('/medinfo/primary.js?v=002') }}"></script>
@endpush('loadjsscripts')

@section('inlinejs')
    @parent
    @include('jqxdatainput.dashboardjs')
@endsection