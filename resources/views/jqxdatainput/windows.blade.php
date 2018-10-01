<div id="changeStateWindow">
    <div>Изменение статуса документа (код формы: <span id="changeStateFormCode"></span>, код МО: <span id="changeStateMOCode"></span>)</div>
    <div style="overflow: hidden;">
        <div style='margin-top: 10px;' class="stateradio" id='performed'>Выполняется</div>
        {{--<div style='margin-top: 10px;' class="stateradio" id='inadvance'>Подготовлен к предварительной проверке</div>--}}
        <div style='margin-top: 10px;' class="stateradio" id='prepared'>Подготовлен к проверке</div>
        <div style='margin-top: 10px;' class="stateradio" id='accepted'>Принят</div>
        <div style='margin-top: 10px;' class="stateradio" id='declined'>Возвращен на доработку</div>
        <div style='margin-top: 10px;' class="stateradio" id='approved'>Утвержден</div>
        <textarea id="statusChangeMessage" style="margin: 10px"></textarea>
        <input style="margin-right: 5px;" type="button" id="SaveState" value="Сохранить" />
        <input id="CancelStateChanging" type="button" value="Отменить" /></td>
        <div id="changeStateAlertMessage" style="margin-top: 5px; display: none"></div>
    </div>
</div>
{{--<div id="changeAuditStateWindow">
    <div>Изменение статуса проверки документа</div>
    <div style="overflow: hidden;">
        <div style='margin-top: 10px;' class="auditstateradio" id='noaudit'>
            <span>Не проверен</span></div>
        <div style='margin-top: 10px;' class="auditstateradio" id='audit_correct'>
            <span>Проверен, замечаний нет</span></div>
        <div style='margin-top: 10px;' class="auditstateradio" id='audit_incorrect'>
            <span>Проверен, имеются замечания</span></div>
        <textarea id="auditChangeMessage" style="margin: 10px"></textarea>
        <input style="margin-right: 5px;" type="button" id="SaveAuditState" value="Сохранить статус" />
        <input id="CancelAuditStateChanging" type="button" value="Отменить" /></td>
    </div>
</div>
<div id="BatchChangeAuditStateWindow">
    <div>Изменение статуса проверки документов в сводном отчете</div>
    <div style="overflow: hidden;">
        <div class="jqx-warning">Внимание! Будет изменен статус проверки у всех первичных документов, формирующих данный сводный отчет!</div>
        <div style='margin-top: 10px;' class="batchauditstateradio" id='nobatchaudit'>
            <span>Не проверен</span></div>
        <div style='margin-top: 10px;' class="batchauditstateradio" id='batch_audit_correct'>
            <span>Проверен, замечаний нет</span></div>
        <div style='margin-top: 10px;' class="batchauditstateradio" id='batch_audit_incorrect'>
            <span>Проверен, имеются замечания</span></div>
        <textarea id="AuditBatchChangeMessage" style="margin: 10px"></textarea>
        <input style="margin-right: 5px;" type="button" id="SaveBatchAuditState" value="Изменить статус проверки документов" />
        <input id="CancelBatchAuditStateChanging" type="button" value="Отменить" /></td>
    </div>
</div>--}}
<div id="sendMessageWindow">
    <div>Комментарий/сообщение к документу</div>
    <div style="overflow: hidden;">
        <textarea id="message" style="margin: 10px"></textarea>
        <input style="margin-right: 5px;" type="button" id="SendMessage" value="Отправить" />
        <input id="CancelMessage" type="button" value="Отменить" /></td>
    </div>
</div>