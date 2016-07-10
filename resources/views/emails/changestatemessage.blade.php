Статус документа <a href="http://medinfo.miac-io.ru/formdashboard/{{ $document->id }}">№{{ $document->id }} по Форме №{{ $form->form_code }}</a>
изменен на "{{ $newlabel }}"
<p>Учреждение: {{ $current_unit->unit_name }}</p>
<p>Исполнитель: {{ $worker->description }}</p>
<p>Комментарий исполнителя: {{ $remark }} </p>