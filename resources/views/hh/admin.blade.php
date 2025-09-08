
@extends('hh.layout')
@section('content')

<div class="container">
    <h1 align="center">Список товаров</h1><br>
    <div class="row">
    @if(session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('products.create') }}" method="GET">
        @csrf
        <button type="submit" class="btn btn-success">Добавить товар</button>
    </form>

    @forelse($products as $product)
        <div class="col-md-3">
            <h5>{{ $product->name }}</h5>

            <div>
                <img src="{{  $product->getImage() }}" alt="" width="160" class="img-thumbnail">
            </div>
            <a href="{{route('products.show', $product->id)}}" class='btn btn-info btn-sm mybutton'>Подробнее</a> 
            <a href="{{route('products.edit', $product->id)}}" class='btn btn-warning btn-sm mybutton'>Редактировать</a> 
            <form action="{{ route('products.destroy', $product->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Вы уверены?')" class='btn btn-danger btn-sm mybutton'>Удалить</button>
            </form>
        </div>
    @empty
        No Data.
    @endforelse


</div>
</div>

@endsection