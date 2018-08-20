@extends('jqxadmin.app')

@section('title', 'Загрузка данных из формата Медстат')
@section('headertitle', 'Менеджер загрузки данных из формата Медстат (ЦНИИОИЗ)')

@section('content')
    <div class="col-sm-offset-1 col-sm-9">
        <div class="panel panel-default" style="max-height:850px; overflow: auto; padding: 20px">
            <div class="page-header">
                <h3>Результат загрузки структуры отчетных форм из формата Медстат (Новосибирск)</h3>
            </div>
            <div class="panel-body">
                <p class="text text-info">Загрузка завершена.</p>
                <p class="text text-info">Загружено записей:</p>
                <ul>
                    <li>Формы: {{ $form_count }}</li>
                    <li>Таблицы: {{ $table_count }} (транспонированные: {{ $tansposed_nsktables }})</li>
                    <li>Строки: {{ $row_count }}</li>
                    <li>Графы: {{ $column_count }}</li>
                </ul>
                <p class="text text-info">Сопоставлено кодов:</p>
                <ul>
                    <li>Формы: {{ $matched_forms }}</li>
                    <li>Таблицы: {{ $matched_tables }}</li>
                    <li>Строки: {{ $matched_rows }}</li>
                    <li>Графы: {{ $matched_columns }}</li>
                </ul>
                <h4>Выявлены следующие несоответствия структуры:</h4>
                <p>Не сопоставлены формы (отсутствуют в Медстат (НСК)):</p>
                <ol>
                    @foreach ($form_exists_only_mf as $mff)
                        <li>({{ $mff->form_code }}) {{ $mff->form_name }}</li>
                    @endforeach
                </ol>
                <p>Не сопоставлены формы (отсутствуют в Мединфо):</p>
                <ol>
                    @foreach ($form_exists_only_nsk as $nskf)
                        <li>({{ $nskf->form_name }}) {{ $nskf->decipher }}</li>
                    @endforeach
                </ol>
                <p>Не сопоставлены таблицы (отсутствуют в Медстат (НСК)):</p>
                <ol>
                    @foreach ($table_exists_only_mf as $mft)
                        <li>({{ $mft->form->form_code }}) {{ $mft->table_code }} {{ $mft->table_name }}</li>
                    @endforeach
                </ol>
                <p>Не сопоставлены таблицы (отсутствуют в Мединфо):</p>

                <ol>
                    @foreach ($table_exists_only_nsk as $nskt)
                        <li> ({{ $nskt->formnsk->form_name }}) {{ $nskt->tablen }} {{ $nskt->name }}</li>
                    @endforeach
                </ol>
                <p>Не сопоставлено транспонирование таблиц:</p>
                @if (count($transposed_disparity) === 0 )
                    <p class="text-success">Не выявлено</p>
                @endif
                <ol>
                    @foreach ($transposed_disparity as $td)
                        <li>({{ $td['form_code']  }}){{ $td['table_code'] }} <span class="text text-danger">{{ $td['comment'] }}</span></li>
                    @endforeach
                </ol>
                <p>Несоответствие по составу строк:</p>
                <ol>
                    @foreach ($rows_disparity as $rd)
                        <li>
                            ({{ $rd['form_code']  }}){{ $rd['table_code'] }}
                            <span class="text text-danger">Число строк в Мединфо: <strong>{{ $rd['mf_count'] }}</strong></span>
                            <span class="text text-danger">Число строк в Медстат (НСК): <strong>{{ $rd['nsk_count'] }}</strong></span>
                        </li>
                    @endforeach
                </ol>
                <p>Несоответствие по составу граф:</p>
                <ol>
                    @foreach ($columns_disparity as $cd)
                        <li>
                            ({{ $cd['form_code']  }}){{ $cd['table_code'] }}
                            <span class="text text-danger">Число граф в Мединфо: <strong>{{ $cd['mf_count'] }}</strong></span>
                            <span class="text text-danger">Число граф в Медстат (НСК): <strong>{{ $cd['nsk_count'] }}</strong></span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
@endsection
