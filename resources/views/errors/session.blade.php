@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}
    </div>
    <?php session()->forget('error'); ?>
@elseif (session('success'))
    <div class="alert alert-success">{{ session('success') }}
    </div>
    <?php session()->forget('success'); ?>
@elseif (session('pay'))
    <div class="alert alert-warning">{{ session('pay') }}
        <b><a href="{{route('price')}}" target="_blank"> Приобрести полный доступ</a></b>
    </div>
    <?php session()->forget('pay'); ?>
@endif
