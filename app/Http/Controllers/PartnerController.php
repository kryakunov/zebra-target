<?php

namespace App\Http\Controllers;

use App\User;
use App\Withdraw;
use App\Payment;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public $users = [];//[100011689, 370003517, 301974853, 798707702, 797915585];


    public function partner()
    {
        if (in_array(session('id'), $this->users)) {
            session(['success' => 'Для вас работают индивидуальные условия: 100% реферальных вознаграждений']);
        }

        $referals = User::where('ref', '=', session('id'))->get();
        $payments = Payment::where('ref', '=', session('id'))->get();
        $withdraws = Withdraw::where('vk_id', '=', session('id'))->get();
        $userPayments = [];

        foreach($referals as $ref){
            if($ref->payment->first()) {
                $userPayments[$ref->payment->first()->user_id] = count($ref->payment->all());
            }
        }

        $payments->map(function($payment){
            $payment['photo'] = User::where('vk_id', '=', $payment->user_id)->first()->photo;
            $payment['first_name'] = User::where('vk_id', '=', $payment->user_id)->first()->first_name;
            $payment['last_name'] = User::where('vk_id', '=', $payment->user_id)->first()->last_name;
            return $payment;
        });

    
        $user = User::where('vk_id', '=', session('id'))->first();
		$ref = User::where('vk_id', '=', $user->ref)->first();

        return view('partner', [
            'referals' => $referals, 
            'user' => $user, 
			'ref' => $ref,
            'userPayments' => $userPayments, 
            'payments' => $payments,
            'withdraws' => $withdraws,
        ]);
    }


    public function withdraw(){

        $user = User::where('vk_id', '=', session('id'))->first();
        $balance = $user->balance;

        return view('withdraw', ['balance' => $balance]);

    }


    public function withdrawStore(Request $request) {
        
        $this->validate($request, [
            'summ' => 'required',
            'system' => 'required',
            'req' => 'required',
        ]);

        $user = User::where('vk_id', '=', session('id'))->first();
        $balance = $user->balance;


        if ($request->input('summ') > $balance || $request->input('summ') < 1 ) {

            return redirect()->route('withdraw')->with('error', 'Не хватает средств');
        }

        if ($request->input('summ') < 200 || $request->input('summ') < 1 ) {

            return redirect()->route('withdraw')->with('error', 'Минимальная сумма для вывода 200 рублей')->withInput();
        }

        $result = Withdraw::create([
            'vk_id' => session('id'),
            'amount' => $request->input('summ'),
            'status' => 0,
            'method' => $request->input('system'),
            'req' => $request->input('req'),
        ]);

        $user->update([
            'balance' => $user->balance - $request->input('summ'),
        ]);

        return redirect()->route('partner')->with('success', 'Заявка успешно создана');
    }


    public function withdrawSuccess($id){
        
        $withdraws = Withdraw::where('id', '=', $id);
        $withdraws->update([
            'status' => 1,
        ]);

        return redirect()->route('admin')->with('success', 'Успешно');
    }

    public function payAccess(){
        
        return view('pay-access');
    }

    public function payAccessStore(Request $request){
        
        $package = $request->get('package');

        switch ($package) {
            case 1:
                $amount = 199;
                break;
            case 2:
                $amount = 399;
                break;
            case 3:
                $amount = 599;
                break;
            case 4:
                $amount = 1299;
                break;
        }

        $user = User::where('vk_id', '=', session('id'))->first();
        $balance = $user->balance;

        if ($amount > $balance) {
            return redirect()->route('pay-access')->with('error', 'Не хватает средств');
        }

        $transaction = session('id') . '_' . time() . '_' . $package;
		$reward = round($amount * (30 / 100));
        $ref = $user->ref;

        // Создаем оплату
        $result = Payment::create([
            'user_id' => session('id'),
            'amount' => $amount,
            'package' => $package,
            'transaction' => $transaction,
            'reward' => $reward,
            'ref' => $ref,
        ]);

        // Высчитываем доступ до какого числа
        $access = $user->access;

        if ($access < time()) {
            $access = time();
        }

        switch ($package) {
            case 1:
                $access = strtotime("+1 month", $access);
                break;
            case 2:
                $access = strtotime("+3 month", $access);
                break;
            case 3:
                $access = strtotime("+6 month", $access);
                break;
            case 4:
                $access = strtotime("+12 month", $access);
                break;
        }


        // Обновляем данные пользователя
        $user = User::where('vk_id', '=', session('id'))->first();
        $user->update([
            'access' => $access,
            'package' => $package,
            'balance' => $user->balance - $amount,
        ]);

        // Начисляем реферальные
        $user = User::where('vk_id', '=', $ref)->first();
        $user->update([
            'balance' => $user->balance + $reward,
        ]);      

        return redirect()->route('payment-success');
    }
}
