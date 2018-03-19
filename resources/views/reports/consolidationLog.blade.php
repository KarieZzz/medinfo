@extends('reports.report_layout')

@section('content')
    <div class="row" style="margin-top: 20px">
        <h3>Журнал консолидации</h3>
        <div id="documentLog">
            <table class="table table-bordered table-condensed table-striped">
                <tr>
                    <th>Код</th>
                    <th>Субъект</th>
                    <th>Значение</th>
                </tr>
                @foreach($log as $unit)
                    <tr>
                        <td>{{ $unit['unit_code']}}</td>
                        <td>{{ $unit['unit_name'] }}</td>
                        <td>{{ $unit['value'] }}</td>
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
