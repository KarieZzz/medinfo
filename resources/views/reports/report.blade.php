@extends('reports.report_layout')

@section('description')
    Консолидированный отчет Мединфо
@endsection

@section('title')
    <div class="col-sm-7"><h2>Консолидированный отчет Мединфо</h2></div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-9">
            <h4>{{ $title }}</h4>
        </div>
    </div>
    @include('reports.calc_error_alert')
    <div class="row">
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th width="300px">Территория/Медицинская организация</th>
                {{--<th>ИНН</th>--}}
                @foreach( $structure['content'] as $index => $description)
                    <th title="{{ $description['value'] }}" width="100px">{{ $description['title'] }} </th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($indexes as $index)
            <tr>
                <td>{{ $index['unit_name'] }}</td>
                {{--<td>{{ $index['inn'] }}</td>--}}
                @for($i = 0; $i < $count_of_indexes; $i++)
                    <td>{{ $index[$i]['value'] }}</td>
                @endfor
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
