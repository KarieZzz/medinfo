@extends('reports.report_layout')

@section('content')
    <div class="container">
        <h3>Выборочная проверка по форме №{{ $form->form_code }}. {{ $form->form_name }} за период "{{ $period->name }}"</h3>
        <div id="protocol">
            <table class="table table-bordered table-condensed">
                <tr>
                    <th>Медицинская организация </th>
                    <th>Результат проверки</th>
                </tr>
                @foreach($selected_protocol as $res)
                    @if (!$res['valid'])
                    <tr>
                        <td width="400">
                            <a href="http://medinfo.miac-io.ru/datainput/formdashboard/{{ $res['document'] }}" target="_blank">{{ $res['unit_code'] }} {{ $res['unit_name']  }}</a>
                        </td>
                        <td>

                            @foreach($res['protocol'] as $f)
                                @if (!$f['valid']) <div class="alert alert-danger">Ошибка!</div> @endif
                                <div @if (!$res['valid']) class="text-danger" @else class="text-success" @endif >Функция: {{ $f['formula'] }}</div>
                                @foreach($f['iterations'] as $iteration )
                                    <div class="text-warning small"> @if ($f['iteration_mode'] == 1) строка: @elseif ($f['iteration_mode'] == 2) графа: @endif {{$iteration['code'] or '' }}</div>
                                <table class="table-bordered table-condensed">
                                    <tr @if (!$iteration['valid']) class="danger" @else class="success" @endif>
                                        <td>Значение</td>
                                        <td>Знак сравнения</td>
                                        <td>Контрольная сумма</td>
                                        <td>Отклонение</td>
                                        <td>Результат контроля</td>
                                    </tr>
                                    <tr>
                                        <td>{{ $iteration['left_part_value'] }}</td><td>{{ $f['boolean_sign'] }}</td>
                                        <td>{{ $iteration['right_part_value'] }}</td><td>{{ $iteration['deviation'] }}</td>
                                        <td>@if (!$iteration['valid']) <p class="text-danger">ошибка</p>  @else <p class="text-success">верно</p> @endif</td>
                                    </tr>
                                </table>
                                @endforeach
                                <div class="text-warning small">Комментарий: {{ $f['comment'] }}</div>

                            @endforeach
                        </td>
                    </tr>
                    @endif
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
