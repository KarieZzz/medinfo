<div id="changeStateWindow">
    <div>Изменение статуса документа</div>
    <div style="overflow: hidden;">
        <div style='margin-top: 10px;' class="stateradio" id='performed'>
            <span>Выполняется</span></div>
        <div style='margin-top: 10px;' class="stateradio" id='prepared'>
            <span>Подготовлен к проверке</span></div>
        <div style='margin-top: 10px;' class="stateradio" id='accepted'>
            <span>Принят</span></div>
        <div style='margin-top: 10px;' class="stateradio" id='declined'>
            <span>Возвращен на доработку</span></div>
        <div style='margin-top: 10px;' class="stateradio" id='approved'>
            <span>Утвержден</span></div>
        <textarea id="statusChangeMessage" style="margin: 10px"></textarea>
        <input style="margin-right: 5px;" type="button" id="SaveState" value="Сохранить" />
        <input id="CancelStateChanging" type="button" value="Отменить" /></td>
    </div>
</div>
<div id="changeAuditStateWindow">
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
</div>
<div id="sendMessageWindow">
    <div>Комментарий/сообщение к документу</div>
    <div style="overflow: hidden;">
        <textarea id="message" style="margin: 10px"></textarea>
        <input style="margin-right: 5px;" type="button" id="SendMessage" value="Отправить" />
        <input id="CancelMessage" type="button" value="Отменить" /></td>
    </div>
</div>