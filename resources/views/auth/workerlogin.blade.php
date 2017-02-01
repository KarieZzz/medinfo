@extends('layout.authlayout')

@section('content')
	<div class="login-box">
		<div class="register-logo">
			<a href="/"><b>Medinfo</b> ВводДанных</a>
		</div>
		<div class="login-box-body">
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
			<p class="login-box-msg">Авторизация пользователя - исполнителя отчета</p>
				<form role="form" method="POST" action="{{ url('/workerlogin') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
					<div class="form-group has-feedback">
						<input type="text" class="form-control" placeholder="Имя пользователя" name="name" value="{{ old('name') }}">
						<span class="glyphicon glyphicon-user form-control-feedback"></span>
					</div>
					<div class="form-group has-feedback">
						<input type="password" class="form-control" placeholder="Пароль" name="password">
						<span class="glyphicon glyphicon-lock form-control-feedback"></span>
					</div>
					<div class="row">
{{--						<div class="col-xs-8 col-xs-offset-0">
							<div class="form-group">
								<label>
									<input type="checkbox" name="remember"> Запомнить меня
								</label>
							</div>
                            <a class="btn btn-link" href="{{ url('/password/email') }}">Забыли Ваш пароль?</a>
						</div>--}}
						<!-- /.col -->
						<div class="col-xs-4">
							<button type="submit" class="btn btn-primary btn-block btn-flat">Войти</button>
						</div>
						<!-- /.col -->
					</div>
				</form>
	</div>
</div>
@endsection
