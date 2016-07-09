@extends('app')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Перечень отчетных форм
        <small>Просмотр, редактирование</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Структура</li>
        <li class="active">Формы</li>
    </ol>
</section>
<!-- Main content -->
<section class="content">
    <!-- /.box -->
        <div class="col-md-7">
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Редактирование параметров отчетной формы</h3>
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                <form role="form" method="post" action="/structure/updateform/{{ $form->id }}">
                    {{ csrf_field() }}
                    {{ method_field('PATCH') }}
                    <div class="box-body">
                        <div class="row">
                            <div class="form-group col-xs-3">
                                <label for="form_code">Код формы</label>
                                <input type="text" class="form-control" id="form_code" name="form_code" placeholder="Введите код формы" value="{{ $form->form_code }}">
                            </div>
                            <div class="form-group col-xs-8">
                                <label for="form_name">Название формы</label>
                                <input type="text" class="form-control" id="form_name" name="form_name" placeholder="Название формы" value="{{ $form->form_name }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-xs-4">
                                <label for="medstat_code">Код формы в программе Медстат</label>
                                <input type="text" class="form-control" id="medstat_code" name="medstat_code" placeholder="Введите код формы в программе Медстат" value="{{ $form->medstat_code }}">
                            </div>
                            <div class="form-group col-xs-4">
                                <label for="medinfo_id">Код формы в программе Мединфо</label>
                                <input type="text" class="form-control" id="medinfo_id" name="medinfo_id" placeholder="Введите код формы Медстат" value="{{ $form->medinfo_id }}">
                            </div>
                            <div class="form-group col-xs-4">
                                <label for="file_name">Имя файла (для экспорта данных) </label>
                                <input type="text" class="form-control" id="file_name" name="file_name" placeholder="Введите код формы Медстат" value="{{ $form->file_name }}">
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                    </div>
                </form>
                @if (count($errors))
                    <div class="alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <!-- /.box -->
        </div>
</section>
<!-- /.content -->
@endsection