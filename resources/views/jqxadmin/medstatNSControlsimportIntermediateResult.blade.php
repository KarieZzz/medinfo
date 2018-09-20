@extends('jqxadmin.app')

@section('title', 'Импорт контролей из формата Медстат (Новосибирск)')
@section('headertitle', 'Менеджер импорта контролей из формата Медстат (Новосибирск)')

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
             <form style="margin-top: 3px" action="/admin/cfunctions/medstatnskimportmake" method="post" enctype="multipart/form-data">
                 <div class="form-group">
                     <label for="error_level">Уровень ошибки импортируемых контролей:</label>
                     <select class="form-control" name="error_level" id="error_level">
                         <option value="1">Ошибка</option>
                         <option value="2">Предупреждение</option>
                     </select>
                 </div>
                 <div class="form-group">
                     <label for="initial_status">Исходный статус импортируемых контролей:</label>
                     <select class="form-control" name="initial_status" id="initial_status">
                         <option value="1">Включен</option>
                         <option value="2">Блокирован</option>
                     </select>
                 </div>
                 <div class="form-group">
                     <label>Виды импортируемых контролей:</label>
                     <div class="checkbox">
                         <label><input type="checkbox" name="control_type_import[]" value="1">Внутритабличные</label>
                     </div>
                     <div class="checkbox">
                         <label><input type="checkbox" name="control_type_import[]" value="2">Межтабличные</label>
                     </div>
                     <div class="checkbox">
                         <label><input type="checkbox" name="control_type_import[]" value="3">Межформенные <span class="text-danger">*</span></label>
                     </div>
                 </div>
                 <div class="form-group">
                     <label for="selectForm">Формы, из которых будут импортированы контроли:</label>
                     <div id="selectForm"></div>
                 </div>
                 <div class="form-group">
                     <button type="button" id="checkAllForm" class="btn btn-default btn-sm">Выбрать все формы</button>
                     <button type="button" id="uncheckAllForm" class="btn btn-default btn-sm">Очистить</button>
                 </div>
                 <input id="formids" name="formids" type="hidden" value="">
                 <input id="selectedallforms" name="selectedallforms" type="hidden" value="">
                 <div class="checkbox">
                     <label><input type="checkbox" name="clear_old_controls" value="1">Удалить контроли из выделенных форм перед импортом </label>
                 </div>
                 <button type="submit" class="btn btn-primary">Импортировать</button>
                 <p class="text-info" style="padding-top: 10px">* - межформенные контроли импортируются все, внезависимости от выбранных форм</p>
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
