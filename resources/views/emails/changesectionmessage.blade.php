Раздел документа <a href="{{ config('app.url') }}/datainput/formdashboard/{{ $document->id }}">№{{ $document->id }} по Форме №{{ $document->form->form_code }}</a>
"{{ $formsection->section_name }}" {{ $action }}
<p>Учреждение: {{ $document->unit->unit_name }}</p>
<p>Исполнитель: {{ $worker->description }}</p>

<p class="text-info">Это письмо создано автоматической системой рассылки при смене статуса отчетного документа. Не отвечайте на него!</p>
<p class="text-info">Для создания сообщения необходимо авторизоваться на портале <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>,
выбрать нужный документ, нажать кнопку на панели инструментов "Создать сообщение/комментарий к документу".</p>