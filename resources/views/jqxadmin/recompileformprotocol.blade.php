@extends('reports.report_layout')

@section('title')
    <div class="col-sm-7"><h2>Протокол перекомпилирования функций контроля по форме {{ $form->form_code }}</h2></div>
@endsection

@section('content')

    @foreach( $protocol as $t => $p )
        <div class="row">
            <div class="col-sm-9">
                <h4>Код таблицы: {{ $t }}</h4>
                <div class="row">
                    <div class="col-md-11">
                        <table class="table table-bordered" style="width: 1300px; min-width: 600px">
                            <thead>
                            <tr>
                                <th style="width: 40px">№</th>
                                <th style="width: 80%">Функция</th>
                                <th>Результат</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $p as $f )
                                <tr  @if (!$f['result']) class="danger" @endif>
                                    <td>{{ $f['i'] }}.</td>
                                    <td>{{ $f['script'] }}</td>
                                    <td>{{ $f['comment'] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endforeach


@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
