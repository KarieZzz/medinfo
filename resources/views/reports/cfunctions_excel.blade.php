@extends('reports.excelexportlayout')

@section('content')
    <table>
        <tr>
            <td><h3>Таблица: {{ $table->table_code }}. {{ $table->table_name  }}. </h3></td>
        </tr>
    </table>
    <table class="data">
        <tr>
            <th>Скрипт</th>
            <th>Комментарий/описание</th>
        </tr>
        @foreach($functions as $function)
            <tr>
                 <td width="120">{{ $function->script }}</td>
                 <td width="120">{{ $function->comment }}</td>
            </tr>
        @endforeach
    </table>
@endsection