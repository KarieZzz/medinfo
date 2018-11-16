<div id="UserProfileWindow">
    <div>Профиль пользователя</div>
    <div style="overflow: auto">
        <div class="panel">
            <div class="row" style="margin: 5px">
                <div class="col-md-12">
                    <form id="userProfileForm">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lastname">Фамилия:</label> <span class="text-danger"><i class="fa fa-star"></i></span>
                                    <input type="text" pattern="^[А-Яа-я-\s]*$" class="form-control" id="lastname" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="firstname">Имя:</label> <span class="text-danger"><i class="fa fa-star"></i></span>
                                    <input type="text" pattern="^[А-Яа-я-\s]*$" class="form-control" id="firstname" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="patronym">Отчество:</label>
                                    <input type="text" pattern="^[А-Яа-я-\s]*$" class="form-control" id="patronym">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="wtel">Телефон рабочий:</label> <span class="text-danger"><i class="fa fa-star"></i></span>
                                    <input type="tel" pattern="^[0-9-+\s()]*$" class="form-control" id="wtel" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ctel">Телефон сотовый:</label>
                                    <input type="tel" pattern="^[0-9-+\s()]*$" class="form-control" id="ctel">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="email">Адрес email:</label> <span class="text-danger"><i class="fa fa-star"></i></span>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="ou">Медицинская организация/ОУЗ:</label>
                                    <input type="text" class="form-control" id="ou">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="post">Должность:</label>
                                    <input type="text" class="form-control" id="post">
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
                            <div class="col-md-8">
                                <button type="button" class="btn btn-success" id="saveProfile">Сохранить</button>
                                <button type="button" class="btn btn-danger" id="cancelProfileSaving">Отменить</button>
                            </div>
                            <div class="col-md-4">
                                <div style="display: none; margin-left: 10px" id="formloader">
                                    <h5>Загрузка данных <img src='/jqwidgets/styles/images/loader-small.gif' /></h5>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 20px">
                            <div class="col-md-12">
                                <p><span class="text-danger"><i class="fa fa-star"></i></span> - Обязательные поля</p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>