@extends('app')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Словарь строк
        <small>Просмотр, редактирование</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Структура</li>
        <li class="active">Строки</li>
    </ol>
</section>
<!-- Main content -->
<section class="content">
    <!-- /.box -->
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Перечень строк отчетных таблиц Мединфо</h3>
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
                                <th class="sorting_asc" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Id: activate to sort column descending" style="width: 295px;">Id</th>
                                <th class="sorting_asc" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-label="Id Таблицы: activate to sort column descending" style="width: 295px;">Id Таблицы</th>
                                <th class="sorting_asc" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-label="Код строки: activate to sort column descending" style="width: 295px;">Код строки</th>
                                <th class="sorting" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-label="Наименование: activate to sort column ascending" style="width: 361px;">Наименование</th>
                                <th class="sorting" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-label="Код Медстат: activate to sort column ascending" style="width: 321px;">Код Медстат</th>
                                <th class="sorting" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-label="Код Мединфо: activate to sort column ascending" style="width: 255px;">Код Мединфо</th>
                                <th class="sorting" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-label="Дата и время создания: activate to sort column ascending" style="width: 190px;">Создана</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($rows) > 0 )
                                @foreach ($rows as $r)
                            <tr role="row" class="odd">
                                <td class="sorting_1">{{ $r->id }}</td>
                                <td>{{ $r->table_id }}</td>
                                <td>{{ $r->row_code }}</td>
                                <td><a href="/structure/editrow/{{ $r->id }}">{{ $r->row_name }}</a></td>
                                <td>{{ $r->medstat_code }}</td>
                                <td>{{ $r->medinfo_id }}</td>
                                <td>{{ $r->created_at }}</td>
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
                                <th rowspan="1" colspan="1">Id</th>
                                <th rowspan="1" colspan="1">Id Таблицы</th>
                                <th rowspan="1" colspan="1">Код строки</th>
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
                        <div class="dataTables_info" id="example1_info" role="status" aria-live="polite">Показаны единицы с {{ $rows->firstItem() }} по {{ $rows->lastItem() }} из {{ $rows->total() }} </div>
                        <div class="dataTables_info" id="example1_info" role="status" aria-live="polite"></div>

                    </div>
                    <div class="col-sm-7">{{ $rows->links() }}</div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</section>
<!-- /.content -->
@endsection