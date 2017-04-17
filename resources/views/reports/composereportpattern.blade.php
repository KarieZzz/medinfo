@extends('jqxadmin.app')

@section('title', 'Формирование структуры произвольного отчета')
@section('headertitle', 'Формирование структуры произвольного отчета')

@section('content')
    @include('jqxadmin.error_alert')
    <div id="formContainer">
        <div id="queryPropertiesForm" class="panel panel-default" >
            <div class="panel-heading"><h3>Структура произвольного отчета</h3></div>
            <div class="panel-body">
                <div class="container-fluid" style="overflow-y:scroll; height:800px; ">
                    <form class="form-horizontal" action="/reports/patterns" method="POST">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="control-label col-sm-3" for="report_name"><h4>Наименование отчета:</h4></label>
                            <div class="col-sm-8">
                                <textarea rows="3" style="font-size: larger" class="form-control" id="report_name" name="report_name"></textarea>
                            </div>
                        </div>
                        <hr>
                        <div class="indexes">
                            <div class="index">
                                <div class="form-group">
                                    <label class="control-label col-sm-2" for="title1">Показатель 1:</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="title1" name="title[]">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-2" for="value1">Значение (формула расчета):</label>
                                    <div class="col-sm-8">
                                        <textarea rows="2" class="form-control" id="value1" name="value[]"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" id="make" class="btn btn-primary">Сохранить</button>
                                <button type="submit" id="makeperform" class="btn btn-default">Сохранить и выполнить</button>
                                <button type="button" id="addindexes" class="btn btn-success">Добавить показатели</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </form>
@endsection

@push('loadjsscripts')
{{--    <script src="{{ asset('/jqwidgets/jqxdata.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxpanel.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxscrollbar.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxinput.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxbuttons.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdropdownbutton.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxcheckbox.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxswitchbutton.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxlistbox.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdropdownlist.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.filter.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.columnsresize.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.selection.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.sort.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdatatable.js') }}"></script>--}}
    <script src="{{ asset('/jqwidgets/localization.js') }}"></script>
    <script src="{{ asset('/medinfo/admin/composereportpattern.js?v=002') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        initActions();
    </script>
@endsection
