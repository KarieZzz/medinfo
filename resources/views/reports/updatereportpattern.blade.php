@extends('jqxadmin.app')

@section('title', 'Формирование структуры произвольного отчета')
@section('headertitle', 'Формирование структуры произвольного отчета')

@section('content')
    @include('jqxadmin.error_alert')
    @include('jqxadmin.status_alert')
    <div id="queryPropertiesForm" class="panel panel-default" >
        <div class="panel-heading"><h3>Структура произвольного отчета</h3></div>
        <div class="panel-body">
            <div class="container-fluid" style="overflow-y:scroll; height:800px; ">
                <form class="form-horizontal" action="/reports/patterns/{{ $id }}" method="post">
                    {{ method_field('PATCH') }}
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="report_name"><h4>Наименование отчета:</h4></label>
                        <div class="col-sm-8">
                            <textarea rows="3" style="font-size: larger" class="form-control" id="report_name" name="report_name">{{ $name  }}</textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="indexes">
                        @for($i = 1; count($indexes) >= $i ;$i++ )
                        <div id="index{{ $i }}" class="index">
                            <div class="form-group">
                                <label class="control-label col-sm-2" for="title1">Показатель {{ $i }} (наименование):</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="title1" name="title[]" value="{{ $indexes['index'.$i]['title'] }}">
                                </div>
                                @if ($i > 1)
                                <div class="col-sm-2">
                                    <button type="button" class="rmindex btn btn-danger"><span class="glyphicon glyphicon-remove"></span></button>
                                </div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-2" for="value1">Значение (формула расчета):</label>
                                <div class="col-sm-8">
                                    <textarea rows="2" class="form-control" id="value1" name="value[]">{{ $indexes['index'.$i]['value'] }}</textarea>
                                </div>
                            </div>
                        </div>
                       @endfor
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-6">
                            <button type="submit" name="save" value="1" id="make" class="btn btn-primary">Сохранить</button>
                            <button type="submit" name="saveperform" value="1" id="makeperform" class="btn btn-default">Сохранить и выполнить</button>
                            <button type="button" id="addindexes" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span>Добавить показатели</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('loadjsscripts')
    <script src="{{ asset('/medinfo/admin/composereportpattern.js?v=005') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        addIndex();
        removeIndex();
    </script>
@endsection
