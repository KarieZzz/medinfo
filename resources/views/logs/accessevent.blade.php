@extends('app')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Журнал доступа пользователей в систему
        <small>Просмотр, последние события в начале списка </small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Журнал доступа</li>
    </ol>
</section>
<!-- Main content -->
<section class="content">
    <p>Пользователь: {{ $event->user_id }}</p>
    <p>Дата и время доступа: {{ $event->created_at }}</p>
</section>
<!-- /.content -->
@endsection