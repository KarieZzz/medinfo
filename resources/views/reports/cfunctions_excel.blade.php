@extends('reports.excelexportlayout')

@section('content')
    <table>
        <tr>
            <td><h3>Таблица: {{ $table->table_code }}. {{ $table->table_name  }}. </h3></td>
        </tr>
    </table>
    <table class="data">
        <tr>
            <th>Id</th>
            <th>Функция контроля</th>
            <th>Комментарий/описание</th>
            <th>Уровень (1 - ошибка, 2 - предупреждение)</th>
            <th>Отключен</th>
        </tr>
        @foreach($functions as $function)
            <tr>
                 <td width="10">{{ $function->id }}</td>
                 <td width="105">{{ $function->script }}</td>
                 <td width="105">{{ $function->comment }}</td>
                 <td width="20">{{ $function->level }}</td>
                 <td width="20">{{ $function->blocked }}</td>
            </tr>
        @endforeach
    </table>
@endsection