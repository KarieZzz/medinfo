@extends('jqxadmin.app')

@section('title', 'Импорт контролей из формата Медстат (Новосибирск)')
@section('headertitle', 'Менеджер импорта контролей из формата Медстат (Новосибирск)')

@section('content')
    @include('jqxadmin.error_alert')
    <div class="col-sm-offset-1 col-sm-9">
        <div class="panel panel-default" style="max-height:850px; overflow: auto; padding: 20px">
            <h4>Импорт контролей завершен</h4>
            <p>В систему загружено {{ $all }} контролей.</p>
            <p>Их них: </p>
            <p> - межформенных {{ $interform_saved_count }} (конвертация по всем формам).</p>
            <p> - межтабличных {{ $intertables_saved_count }}.</p>
            <p> - внутритабличных {{ $intables_saved_count }}.</p>
            <p> По формам: </p>
            <ol>
                @foreach($forms as $form)
                    <li> ({{ $form->form_code }}) {{ $form->form_name }}</li>
                @endforeach
            </ol>
        </div>
    </div>
@endsection

@push('loadjsscripts')

@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
    </script>
@endsection
