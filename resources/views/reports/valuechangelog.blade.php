@extends('reports.report_layout')

@section('content')
    <div class="row" style="margin-top: 20px">
        <h3>Журнал изменений по форме №{{ $form->form_code }}. {{ $form->form_name }} за период "{{ $period->name }}"</h3>
        <div id="documentLog">
            <table class="table table-bordered table-condensed">
                <tr>
                    <th>Дата и время</th>
                    <th>Сотрудник</th>
                    <th>Документ</th>
                    <th>Медицинская организация</th>
                </tr>
                @foreach($records as $record)
                    <tr>
                        <td>{{ $record->timestamp }}</td>
                        <td>{{ $record->worker }}</td>
                        <td>{{ $record->document }}</td>
                        <td>{{ $record->unit }}</td>
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
