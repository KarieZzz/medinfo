@if (count($calculation_errors) > 0)
    <button data-toggle="collapse" data-target="#demo" class="btn btn-danger">При выполнении вычислений были выявлены ошибки</button>
    <div id="demo" class="collapse">
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($calculation_errors as $cerror)
                            <li>В формуле: {{ $cerror['formula'] }} по ОЕ: {{ $cerror['unit']->unit_name }} . Ошибка: {{ $cerror['error'] }} </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif