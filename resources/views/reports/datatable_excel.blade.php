@extends('reports.excelexportlayout')

@section('content')
    <table>
        <tr>
            <td colspan="{{ count($cols) }}"><h3>Таблица: {{ $table->table_code }}. {{ $table->table_name  }}. </h3></td>
        </tr>
        <tr>
            <td colspan="{{ count($cols) }}" style="color: red">Не для предоставления в МИАЦ в качестве отчетной формы!</td>
        </tr>
    </table>
    <table class="data">
        <tr>
            @foreach($cols as $col)
                <th width="{{ $col->size/7 }}">{{ $col->column_name }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($cols as $col)
                <th>{{ $col->column_index }}</th>
            @endforeach
        </tr>
        @foreach($data as $row)
            <tr>
                @foreach($row as $cell)
                    <td>{{ $cell }}</td>
                @endforeach
            </tr>
        @endforeach
    </table>
@endsection