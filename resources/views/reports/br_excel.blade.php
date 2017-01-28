@extends('reports.report_layout')

@section('content')
<div class="container">
    <table class="table table-bordered table-condensed">
        <tr>
            <th colspan="4"><h3>Справка по форме №{{ $form->form_code }}. {{ $form->form_name }} за период "{{ $period->name }}"</h3></th>
        </tr>
        <tr>
            <th colspan="4"><h4>Таблица: {{ $table->table_code }}. {{ $table->table_name  }}. </h4></th>
        </tr>
        <tr>
            <th colspan="4"><h4>{{ $group_title }} {{ $el_name }}</h4></th>
        </tr>
        <tr>
            <th colspan="4"><h4>Ограничение по территории: {{ $top->unit_name }}</h4></th>
        </tr>
        <tr>
            <th>Код</th>
            <th>Субъект</th>
            @foreach($column_titles as $title)
                <th>{{ $title }}</th>
            @endforeach
        </tr>
        @foreach($units as $unit)
            <tr>
                <td width="7" align="right">{{ $unit->unit_code }}</td>
                <td width="100">{{ $unit->unit_name }}</td>
                @foreach($values[$unit->id] as $v)
                    <td>{{ $v }}</td>
                @endforeach
            </tr>
        @endforeach
    </table>
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
