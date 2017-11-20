@extends('reports.excelexportlayout')

@section('content')
<div class="container">
    <table>
        <tr>
            <td colspan="{{ count($column_titles)+2 }}"><h2>Справка по форме №{{ $form->form_code }}. {{ $form->form_name }} за период "{{ $period->name }}"</h2></td>
        </tr>
        <tr>
            <td colspan="{{ count($column_titles)+2 }}"><h3>Таблица: {{ $table->table_code }}. {{ $table->table_name  }}. </h3></td>
        </tr>
        <tr>
            <td colspan="{{ count($column_titles)+2 }}"><h4>{{ $group_title }} {{ $el_name }}</h4></td>
        </tr>
        <tr>
            <td colspan="{{ count($column_titles)+2 }}"><h4>Ограничение по территории/группе: {{ $top->unit_name or $top->group_name }}</h4></td>
        </tr>
    </table>
    <table class="data">
        <tr>
            <th>Код</th>
            <th>Субъект</th>
            @foreach($column_titles as $title)
                <th>{{ $title }}</th>
            @endforeach
        </tr>
        @foreach($units as $unit)
            <tr>
                <td align="right">{{ $unit->unit_code }}</td>
                <td width="70">{{ $unit->unit_name }}</td>
                @foreach($values[$unit->id] as $v)
                    <td width="18">{{ $v }}</td>
                @endforeach
            </tr>
        @endforeach
        <tr>
            <td colspan="2"><strong>Иркутская область</strong></td>
            @foreach($values[999999] as $aggregate)
                <td><strong>{{ $aggregate }}</strong></td>
            @endforeach
        </tr>
    </table>
    </div>
@endsection