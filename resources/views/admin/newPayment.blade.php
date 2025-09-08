
<div class="title">Новая продажа</div><br>
    <div class="content">
        <form method="post" action="/admin/newpayment">
        {{csrf_field()}}
        <div class="input-group mb-3">
            <input type="text" name="id" class="form-control" placeholder="ID" value="{{old('id')}}">

            <select class="form-select" name="vk_id" aria-label="Default select example">
                    <option value="">-- Выберите из списка --</option>
                @foreach($last_users as $user)
                  <option value="{{ $user->vk_id }}">{{ $user->first_name . ' ' . $user->last_name }}</option>
                @endforeach
            </select>


            <select class="form-select" name="package" aria-label="Default select example">
                <option value="1">1 месяц</option>
                <option value="2">3 месяца</option>
                <option value="3">6 месяцев</option>
                <option value="4">Год</option>
            </select>


            <button class="btn btn-outline-success" type="submit" id="button-addon2" onclick="return confirm('Вы уверены?')" >Новая продажа</button>
        </div>
    </form>
</div>