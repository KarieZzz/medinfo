@extends('jqxadmin.app')

@section('title', 'Выберите раздел из меню')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h3>Сводная информация</h3>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading">Активность пользователей</div>
            <div class="panel-body">
                <p>Всего авторизовалось за прошедшие сутки: {{ $lastDayAccess }}</p>
                <p>Всего авторизовалось за прошедшую неделю: {{ $lastWeekAccess }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading">Редактирование данных</div>
            <div class="panel-body">
                <p>Всего внесено/изменено ячеек за прошедшие сутки: {{ $lastDayCellEditing }}</p>
                <p>Всего внесено/изменено ячеек за прошедшую неделю: {{ $lastWeekCellEditing }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading">Документооборот</div>
            <div class="panel-body">
                <p>Всего произведено изменений статуса документа за прошедшие сутки: {{ $lastDayStateChanging }}</p>
                <p>Всего произведено изменений статуса документа за прошедшую неделю: {{ $lastWeekStateChanging }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
