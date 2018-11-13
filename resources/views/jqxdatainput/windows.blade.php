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
        <input id="CancelStateChanging" type="button" value="Отменить" />
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
        <input id="CancelBatchAuditStateChanging" type="button" value="Отменить" />
    </div>
</div>--}}
<div id="sendMessageWindow">
    <div>Комментарий/сообщение к документу</div>
    <div style="overflow: hidden;">
        <textarea id="message" style="margin: 10px"></textarea>
        <input style="margin-right: 5px;" type="button" id="SendMessage" value="Отправить" />
        <input id="CancelMessage" type="button" value="Отменить" />
    </div>
</div>
<div id="DocumentInfoWindow">
    <div>Сводная информация по документу</div>
    <div style="overflow: auto">
        <div class="panel" id="DocInfo">
            <div class="row" data-toggle="collapse" data-target="#valChangingTable" style="margin-right:-6px">
                <div class="col-md-12">
                    <h4>Последние изменения данных</h4>
                </div>
            </div>
            <div class="row collapse in" style="max-height:280px;overflow:auto;margin-right:-6px" id="valChangingTable">
                <div class="col-md-12">
                    <table class="table table-condensed">
                        <thead>
                        <tr>
                            <th>Дата и время</th>
                            <th>Сотрудник</th>
                            <th>Таблица</th>
                            <th>Строка</th>
                            <th>Графа</th>
                            <th>Старое значение</th>
                            <th>Новое значение</th>
                        </tr>
                        </thead>
                        <tbody id="valueChangingRecords"></tbody>
                    </table>
                </div>
            </div>
            <div class="row" data-toggle="collapse" data-target="#stateChangingTable" style="margin-right:-6px">
                <div class="col-md-12">
                    <h4>Изменения статуса документа</h4>
                </div>
            </div>
            <div class="row collapse in" style="max-height:170px;overflow:auto;margin-right:-6px" id="stateChangingTable">
                <div class="col-md-12">
                    <table class="table table-condensed">
                        <thead>
                        <tr>
                            <th>Дата и время</th>
                            <th>Сотрудник</th>
                            <th>Прежний статус</th>
                            <th>Новый статус</th>
                        </tr>
                        </thead>
                        <tbody id="stateChangingRecords"></tbody>
                    </table>
                </div>
            </div>
            <div class="row" data-toggle="collapse" data-target="#sectionChangingTable" style="margin-right:-6px">
                <div class="col-md-12">
                    <h4>Прием/отклонение разделов документа</h4>
                </div>
            </div>
            <div class="row collapse in" style="max-height:170px;overflow:auto;margin-right:-6px" id="sectionChangingTable">
                <div class="col-md-12">
                    <table class="table table-condensed">
                        <thead>
                        <tr>
                            <th>Дата и время</th>
                            <th>Сотрудник</th>
                            <th>Раздел</th>
                            <th>Действие</th>
                        </tr>
                        </thead>
                        <tbody id="sectionChangingRecords"></tbody>
                    </table>
                </div>
            </div>
        </div>
        {{--<input id="CloseDocInfoWindow" type="button" value="Закрыть" />--}}
    </div>
</div>
<div id="UserProfileWindow">
    <div>Профиль пользователя</div>
    <div style="overflow: auto">
        <div class="panel">
            <div class="row" style="margin: 5px">
                <div class="col-md-12">
                    <form>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lastname">Фамилия:</label>
                                    <input type="text" class="form-control" id="lastname">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="firstname">Имя:</label>
                                    <input type="text" class="form-control" id="firstname">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="secondname">Отчество:</label>
                                    <input type="text" class="form-control" id="secondname">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="wtelefon">Телефон рабочий:</label>
                                    <input type="tel" class="form-control" id="wtelefon">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ctelefon">Телефон сотовый:</label>
                                    <input type="tel" class="form-control" id="ctelefon">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="email">Адрес email:</label>
                                    <input type="email" class="form-control" id="email">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="ou">Медицинская организация:</label>
                                    <input type="text" class="form-control" id="ou">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Описание:</label>
                                    <input type="text" class="form-control" id="description">
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 20px">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-success">Сохранить</button>
                                <button type="button" class="btn btn-danger">Отменить</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>