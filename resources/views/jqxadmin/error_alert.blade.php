@if (count($errors) > 0)
    <div class="alert alert-danger">
        <strong>Ошибка!</strong> Не все необходимые поля заполнены.<br><br>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif