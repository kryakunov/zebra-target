@extends('hh.layout')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">

        <br>
        <form action="{{ route('products.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        Название: <input type="text" name="name" value="" class="form-control"><br>
        Цена: <input type="text" name="price" value="" class="form-control"><br>
        Фото: <input type="file" name="image" value="">
            <br><br>
            <button type="submit" class='btn btn-warning'>Создать</button>
        </form>

        </div>
    </div>
</div>
@endsection