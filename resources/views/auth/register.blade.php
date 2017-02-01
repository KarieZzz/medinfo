@extends('layout.authlayout')

@section('content')
	<div class="register-box">
		<div class="register-logo">
			<a href="/"><b>Medinfo</b> WebAdmin</a>
		</div>
		<div class="register-box-body">
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>Ошибка!</strong> Есть проблемы с вводом Ваших данных.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
			<p class="login-box-msg">Регистрация нового пользователя</p>

			<form method="post" action="{{ url('/register') }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<div class="form-group has-feedback">
					<input type="text" class="form-control" placeholder="Фамилия ИО" name="name" value="{{ old('name') }}">
					<span class="glyphicon glyphicon-user form-control-feedback"></span>
				</div>
				<div class="form-group has-feedback">
					<input type="email" class="form-control" placeholder="Email" name="email" value="{{ old('email') }}">
					<span class="glyphicon glyphicon-envelope form-control-feedback"></span>
				</div>
				<div class="form-group has-feedback">
					<input type="password" class="form-control" placeholder="Пароль" name="password">
					<span class="glyphicon glyphicon-lock form-control-feedback"></span>
				</div>
				<div class="form-group has-feedback">
					<input type="password" class="form-control" placeholder="Повторите пароль" name="password_confirmation">
					<span class="glyphicon glyphicon-log-in form-control-feedback"></span>
				</div>
				<div class="row">
					<!-- /.col -->
					<div class="col-xs-7">
						<button type="submit" class="btn btn-primary btn-block btn-flat">Зарегистрировать</button>
					</div>
					<!-- /.col -->
				</div>
			</form>
			<a href="{{ url('login') }}" class="text-center">У меня уже есть учетная запись</a>
		</div>
		<!-- /.form-box -->
	</div>
	<!-- /.register-box -->
@endsection
