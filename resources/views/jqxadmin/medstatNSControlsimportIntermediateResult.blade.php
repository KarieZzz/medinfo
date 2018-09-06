@extends('jqxadmin.app')

@section('title', 'Загрузка данных из формата Медстат (Новосибирск)')
@section('headertitle', 'Менеджер загрузки данных из формата Медстат (Новосибирск)')

@section('content')
    @include('jqxadmin.error_alert')
    <div class="col-sm-offset-1 col-sm-9">
        <div class="panel panel-default" style="max-height:850px; overflow: auto; padding: 20px">
            <h4>Данные из предоставленного файла получены успешно</h4>
            <p>Обнаружено и удалено полностью, включая левую и правую часть уровнения, знак сравнения, итерацию, дублирующихся контролей: {{ $dubs_count }} .</p>
            <p>В систему загружено {{ $rec_count }} контролей.</p>
            <p>Их них: </p>
            <p> - межформенных {{ $inter_form_count }}.</p>
            <p> - межтабличных {{ $inter_table_count }}.</p>
            <p> - внутритабличных {{ $intable_count }}.</p>
{{--            <ol>
                @foreach($duplicates as $duplicate)
                    <li>
                        Форма: {{ $duplicate->fcode }}. Таблица: {{ $duplicate->table or "меж. таб."}}
                        Левая часть сравнения: {{ $duplicate->left }}, Правая часть сравнения: {!! $duplicate->right !!}, {!! $duplicate->relation !!}, Итерация: {!! $duplicate->cycle !!}
                        повторов {{ $duplicate->repeats }}
                    </li>
                @endforeach
            </ol>--}}
             <h4>Для последующей загрузки необходимо выбрать формы</h4>
             <form style="margin-top: 3px" action="/admin/cfunctions/medstatimportmake" method="post" enctype="multipart/form-data">
                 <div class="form-group">
                     <label for="error_level">Установить уровень ошибки для импортируемых контролей:</label>
                     <select class="form-control" name="error_level" id="error_level">
                         <option value="1">Ошибка</option>
                         <option value="2">Предупреждение</option>
                     </select>
                 </div>
                 <div class="form-group">
                     <label for="selectForm">Загружать данные из выбранных форм</label>
                     <div id="selectForm"></div>
                 </div>
                 <div class="form-group">
                     <button type="button" id="checkAllForm" class="btn btn-default btn-sm">Выбрать все формы</button>
                     <button type="button" id="uncheckAllForm" class="btn btn-default btn-sm">Очистить</button>
                 </div>
                 <input id="formids" name="formids" type="hidden" value="">
                 <input id="selectedallforms" name="selectedallforms" type="hidden" value="">
                  <button type="submit" class="btn btn-primary">Импортировать</button>
            </form>
        </div>
        </div>
@endsection

@push('loadjsscripts')
    <script src="{{ asset('/medinfo/admin/medstatnskstructinport.js?v=004') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let forms = {!! $forms  !!};
        let allforms = $("#selectedallforms");
        let sel = $("#selectForm");
        let formsids = $("#formids");
        initdatasources();
        initcontrols();
    </script>
@endsection
