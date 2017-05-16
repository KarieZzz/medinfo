@extends('reports.report_layout')

@section('content')
    <div class="row" style="margin-top: 20px">
        <a href="/reports/br/querycomposer" class="btn btn-info" role="button">Сформировать новую справку</a>
        <h3>Справка по форме №{{ $form->form_code }}. {{ $form->form_name }} за период "{{ $period->name }}"</h3>
        <h4>Таблица: {{ $table->table_code }}. {{ $table->table_name  }}. </h4>
        <h4>{{ $group_title }} {{ $el_name }}</h4>
        <h4>Ограничение по территории/группе: {{ $top->unit_name or $top->group_name }}</h4>
        <div id="documentLog">
            <table class="table table-bordered table-condensed table-striped">
                <tr>
                    <th>Код</th>
                    <th>Субъект</th>
                    @foreach($column_titles as $title)
                        <th>{{ $title }}</th>
                    @endforeach
                </tr>
                @foreach($units as $unit)
                    <tr>
                        <td>{{ $unit->unit_code }}</td>
                        <td>{{ $unit->unit_name }}</td>
                        @foreach($values[$unit->id] as $v)
                            <td>{{ is_numeric($v) ? number_format($v, 2, ',', '') : $v  }}</td>
                        @endforeach
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2"><strong>Иркутская область</strong></td>
                    @foreach($values[999999] as $aggregate)
                        <td><strong>{{ is_numeric($aggregate) ? number_format($aggregate, 2, ',', '') : $aggregate  }}</strong></td>
                    @endforeach
                </tr>
            </table>
        </div>
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
