

@extends('myworks.ajax')

<script type="text/javascript">
    function mode() {
        $.ajax({
            url: 'https://zebra-target.ru/ajax',
            success: function(data) {
                $('#display').html(data);
                var res = data;
            }
        });
    };

    setInterval(mode, 1);
</script>



<button onclick="alert(res)">Выведи переменную на экран</button>


@section('content')
в
<div id="display">
	ы
</div>

@endsection