@extends('reports.report_layout')

@section('title')
    <div class="col-sm-7"><h2>Протокол перекомпилирования функций контроля</h2></div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-9">
            <h4>Форма: {{ $form->form_code }}, Код таблицы: {{ $table->table_code }} ({{ $table->table_name }})</h4>
        </div>
    </div>
    <div class="row">
        <div class="col-md-11">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>№</th>
                    <th>Функция</th>
                    <th>Результат</th>
                </tr>
                </thead>
                <tbody>
                    @foreach( $protocol as $f )
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
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
