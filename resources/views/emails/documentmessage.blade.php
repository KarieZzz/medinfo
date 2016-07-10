Пользователь {{ $worker->description }} оставил сообщение:
<p> {{ $remark }} </p>
<p>Для документа <a href="http://medinfo.miac-io.ru/formdashboard/{{ $document->id }}">№{{ $document->id }}}</a> по Форме №{{ $form->form_code }}</p>
<p>Учреждение: {{ $unit->unit_name }}</p>