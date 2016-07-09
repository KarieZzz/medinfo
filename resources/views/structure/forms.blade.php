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
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Отчетные формы Мединфо</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div id="example1_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class="row"><div class="col-sm-6">
                    <div class="dataTables_length" id="example1_length">
                        <label>Показать
                            <select name="example1_length" aria-controls="example1" class="form-control input-sm">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select> записей
                        </label></div></div>
                    <div class="col-sm-6">
                        <div id="example1_filter" class="dataTables_filter">
                        <label>Поиск:<input type="search" class="form-control input-sm" placeholder="" aria-controls="example1">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <table id="example1" class="table table-bordered table-striped dataTable" role="grid" aria-describedby="example1_info">
                        <thead>
                            <tr role="row">
                                <th class="sorting_asc" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Код формы: activate to sort column descending" style="width: 295px;">Код формы</th>
                                <th class="sorting" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-label="Наименование: activate to sort column ascending" style="width: 361px;">Наименование</th>
                                <th class="sorting" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-label="Код Медстат: activate to sort column ascending" style="width: 321px;">Код Медстат</th>
                                <th class="sorting" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-label="Код Мединфо: activate to sort column ascending" style="width: 255px;">Код Мединфо</th>
                                <th class="sorting" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-label="Дата и время создания: activate to sort column ascending" style="width: 190px;">Создана</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($forms) > 0 )
                                @foreach ($forms as $f)
                            <tr role="row" class="odd">
                                <td class="sorting_1">{{ $f->form_code }}</td>
                                <td><a href="/structure/editform/{{ $f->id }}">{{ $f->form_name }}</a></td>
                                <td>{{ $f->medstat_code }}</td>
                                <td>{{ $f->medinfo_id }}</td>
                                <td>{{ $f->created_at }}</td>
                                @endforeach
                            @else
                                <td class="sorting_1"></td>
                                <td>Нет записей</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            @endif
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <th rowspan="1" colspan="1">Код формы</th>
                                <th rowspan="1" colspan="1">Наименование</th>
                                <th rowspan="1" colspan="1">Код Медстат</th>
                                <th rowspan="1" colspan="1">Код Мединфо</th>
                                <th rowspan="1" colspan="1">Создана</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info" id="example1_info" role="status" aria-live="polite">Показаны единицы с {{ $forms->firstItem() }} по {{ $forms->lastItem() }} из {{ $forms->total() }} </div>
                        <div class="dataTables_info" id="example1_info" role="status" aria-live="polite"></div>

                    </div>
                    <div class="col-sm-7">{{ $forms->links() }}</div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</section>
<!-- /.content -->
@endsection