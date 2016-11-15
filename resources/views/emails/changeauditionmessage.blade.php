Проведена проверка документа <a href="http://medinfo.miac-io.ru/datainput/formdashboard/{{ $document->id }}">№{{ $document->id }} по Форме №{{ $form->form_code }}</a>.
Статус проверки: "{{ $newlabel }}".
<p>Учреждение: {{ $current_unit->unit_name }}</p>
<p>Эксперт: {{ $worker->description }}</p>
<p>Замечания эксперта: {{ $remark }} </p>