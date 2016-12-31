<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Протокол сопоставления структуры Медстат и Мединфо</title>
<link href="{{ asset('/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
<body>
<div class="container">
    <h3>Протокол сопоставления структуры формы № {{ $form->form_code }}</h3>
    @foreach( $tables as $t )
    <div id="tableLog">
        <h4>Таблица: ({{ $t->table_code }}) {{ $t->table_name }}. Код медстат: {{ $t->medstat_code }}. Таблица транспонирована: {{ $t->transposed }} </h4>
        @if (count($errors[$t->id]) > 0)
            @foreach( $errors[$t->id] as $error )
                <div class="alert alert-danger">
                    <strong>Ошибка!</strong> {{ $error }}
                </div>
            @endforeach
        @endif
        <table class="table table-bordered table-condensed">
            <tr>
                <th>Индекс</th>
                <th>Код</th>
                <th>Наименование</th>
                <th>Код Медстат</th>
                <th>Словарь Медстат</th>
                <th>Наименование Медстат</th>
                <th>Год</th>
                <th>Код строки</th>
            </tr>
            @foreach( $matching_array[$t->id] as $r )
                <tr @if (isset($r[3]) && isset($r[7]) && $r[3] <> $r[7]) class="danger" @endif >
                    <td>{{ $r[0] or 'Отсутствует значение' }}</td>
                    <td>{{ $r[1] or 'Отсутствует значение' }}</td>
                    <td>{{ $r[2] or 'Отсутствует значение' }}</td>
                    <td>{{ $r[3] or 'Отсутствует значение' }}</td>
                    <td>{{ $r[4] or 'Отсутствует значение' }}</td>
                    <td>{{ $r[5] or 'Отсутствует значение' }}</td>
                    <td>{{ $r[6] or 'Отсутствует значение' }}</td>
                    <td>{{ $r[7] or 'Отсутствует значение' }}</td>
                </tr>
            @endforeach
        </table>
    </div>
    @endforeach
</div>
<script src="{{ asset('/plugins/jquery/jquery-1.12.4.min.js') }}" type="text/javascript" ></script>
<script src="{{ asset('/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>
</body>
</html>