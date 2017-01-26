@extends('reports.report_layout')

@section('content')
    <div class="container">
        <h3>Справка по форме №{{ $form->form_code }}. {{ $form->form_name }} за период "{{ $period->name }}"</h3>
        <h4>Таблица: {{ $table->table_code }}. {{ $table->table_name  }}. </h4>
        <h4>{{ $group_title }} {{ $el_name }}</h4>
        <div id="documentLog">
            <table class="table table-bordered table-condensed">
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
                            <td>{{ $v }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
