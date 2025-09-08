<?php

namespace App\Http\Controllers;

use App\Update;
use App\support;
use App\Topic;
use App\Payment;
use App\utm;
use App\User;
use App\Withdraw;
use App\mywork;
use App\Http\Exec\Process;
use App\Cloud;
use App\sample_work;
use App\Sample;
use App\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Jobs\SubJob;
use App\Jobs\TestJob;
use App\Chain;
use App\Jobs\JoJob;


class AdminController extends Controller
{
    public function getPaymentUsers()
    {
        $users = ['52090716', '573204714', '185466160', '18277740', '339924512'];
        if (!in_array(session('id'), $users)) dd('Вход запрещен');
        return view('getpaymentusers');
    }

    public function getPaymentUsersPost(Request $request)
    {

        $min = 0;
        $max = 99991689120000;

        ($request->get('time_min') == null) ? $time_min = '2019-01-01' : $time_min = $request->get('time_min');
        ($request->get('time_max') == null) ? $time_max = date('Y-m-d', time()) : $time_max = $request->get('time_max');

        $users = Payment::select('user_id')->whereDate('created_at', '>=', $time_min)->whereDate('created_at', '<=', $time_max)->orderBy('id', 'desc')->get()->toArray();
       $temp = [];
       foreach($users as $user){
        $temp[] = $user['user_id'];
       }
        $users = array_unique($temp);

        return view('getpaymentusers', ['users' => $users, 'request' => $request]);
    }

    public function storePaymentUsers(Request $request)
    {
        $track_id = time();
        $cloudFile = 'cloud/' . session('id') . '_' . $track_id . '.txt';

        Storage::disk('local')->put($cloudFile, $request['users']);

        Cloud::create([
            'name' => 'Список оплативших сервис (' . $request['count'] . ') человек',
            'vk_id' => session('id'),
            'track_id' => $track_id,
            'count' => $request['count'],
            'type_id' => 12,
        ]);

        return redirect()->route('cloud.index')->with('success', 'Сохранено');
    }


    public function job(){


        dispatch(new JoJob);


        return redirect()->route('price');
    }

    public function updates(){

        $data = Update::take(100)->orderBy('id', 'DESC')->get();

        return view('admin5', ['data' => $data]);
    }

    public function updatesStore(Request $request){

        $this->validate($request, ['message' => 'required']);

        Update::create([
            'title' => $request['title'],
            'text' => $request['message'],
            'vk_id' => $request['vk_id'],
        ]);

        return redirect()->route('updates')->with('success', 'Успешно');
    }


    public function updatesDelete($id){

        $item = Update::find($id)->delete();

        return redirect()->route('updates')->with('success', 'Успешно');
    }


    public function index()
    {

        $users = User::orderBy('id', 'desc')->paginate(10);
        $new_users = User::orderBy('id', 'desc')->take(10)->get();
        $last_users = User::where('last_seen', '>', time() - 86400)->get();
        $withdraws = Withdraw::where('status', '=', 0)->orderBy('id', 'desc')->get()->toArray();
        $questions = Topic::where('status', '=', 0)->get();
        $payments = Payment::orderBy('id', 'desc')->take(10)->get()->toArray();
        $tokens = Token::where('status', '=', 'free')->get()->all();
        $countTokens = count($tokens);


        $data = [];
        $i = 0;
        $summ = 0;
        $refka = 0;
        foreach($payments as $payment) {

           $user = User::where('vk_id', '=', $payment['user_id'])->first();
           $data[$i]['count'] = Payment::where('user_id', '=', $user['vk_id'])->count();
           $data[$i]['first_name'] = $user['first_name'];
           $data[$i]['last_name'] = $user['last_name'];
           $data[$i]['vk_id'] = $user['vk_id'];
           $data[$i]['photo'] = $user['photo'];
           $data[$i]['reg'] = $user['reg'];
           $ref = null;
           if (is_numeric($user['ref'])){
            $ref = User::where('vk_id', '=', $user['ref'])->first();
            if ($ref) $ref = $ref->toArray();
            $refSumm = round($payment['amount'] * (30 / 100));
            $refka += $refSumm;
            $payment['amount'] = $payment['amount'] - $refSumm;
           }
           $data[$i]['amount'] = $payment['amount'];
           $summ += $payment['amount'];
           $data[$i]['ref'] = $ref;
           $data[$i]['package'] = $payment['package'];
           $data[$i]['date'] = $payment['created_at'];
           $utm = utm::where('vk_id', '=', $user['vk_id'])->first();
           if ($utm !== null) $utm = $utm->toArray();
           $data[$i]['utm'] = $utm;
           ++$i;
        }

        return view('admin', [
            'last_users' => $last_users,
            'new_users' => $new_users,
            'withdraws' => $withdraws,
            'questions' => $questions,
            'payments' => $data,
            'refka'    => $refka,
            'time_min' => date('Y-m-d', time() - (86400 * 31)),
            'time_max' => date('Y-m-d', time()),
            'summ' => $summ,
            'countTokens' => $countTokens,
            'users' =>      $users,
        ]);
    }


    public function downloadexecfile($id)
    {
        $work = mywork::where('id', '=', $id)->first();
        if (!$work) return redirect()->back()->with('error', 'Данные не найдены');

        $data = [];
        foreach($work->stream as $stream){
            $file = 'logs/'.$work['id'].'-'.$stream['id'] . '.txt';
            $file = file_get_contents($file);

            $data[] = $file;
        }



        dd($data);

    }


    public function downloadlog($id)
    {
        $work = mywork::where('id', '=', $id)->first();
        if (!$work) return redirect()->back()->with('error', 'Данные не найдены');

        $file = '../storage/app/logs/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $log = file_get_contents($file);
        dd($log);
        $name = $work->name . '-log.txt';

        if (!file_exists($file)) return redirect()->back()->with('error', 'Данные не найдены');

        return response()->download($file, $name);
    }

    public function GetSampleLog($id)
    {
        $work = sample::where('id', '=', $id)->first();
        if (!$work) return redirect()->back()->with('error', 'Данные не найдены');

        $file = '../storage/app/samplelogs/'.$work['id']. '.txt';
        $log = file_get_contents($file);
        dd($log);
        $name = $work->name . '-log.txt';

        if (!file_exists($file)) return redirect()->back()->with('error', 'Данные не найдены');

        return response()->download($file, $name);
    }



    public function downloadsourcefile($id)
    {
        $work = mywork::where('id', '=', $id)->first();
        if (!$work) return redirect()->back()->with('error', 'Данные не найдены');

        $file = '../storage/app/sourceworks/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $log = file_get_contents($file);
        $request = unserialize($work['request']);
        dd('Параметры парсинга: ',$request, 'Вставленные значения:',$log);
      //  dd($log);
        $name = $work->name . '-log.txt';

        if (!file_exists($file)) return redirect()->back()->with('error', 'Данные не найдены');

        return response()->download($file, $name);
    }



    public function getworkhistory($id)
    {
        $works = mywork::where('vk_id', '=', $id)->orderBy('id', 'DESC')->get();

        return view('admin3', ['works' => $works, 'all' => true]);
    }


    public function GetSampleWorks()
    {
        $works = sample_work::orderBy('id', 'desc')->take(50)->get();

        return view('admin.SampleWorks', ['works' => $works]);
    }

    public function getpartners()
    {
        $partners = User::where('balance', '>', 0)->orderBy('balance', 'DESC')->get()->toArray();

        return view('admin4', ['partners' => $partners, 'refka' => 0, 'stats' => [] ]);
    }


    public function getAllStats()
    {
        $payments = Payment::orderBy('id', 'desc')->where('created_at', '>', '2023')->get()->toArray();
        $stats = [];
        $refka = 0;
        foreach($payments as $payment)
        {

            $time = strtotime($payment['created_at']);
            $mounth = date('Y-m', $time);

           $user = User::where('vk_id', '=', $payment['user_id'])->first();


            if (isset($user['ref']))
            {
                $ref = User::where('vk_id', '=', $user['ref'])->first();
                if ($ref) $ref = $ref->toArray();
                $refSumm = round($payment['amount'] * (30 / 100));
                $refka += $refSumm;
                $payment['amount'] = $payment['amount'] - $refSumm;
            }

            if (!array_key_exists($mounth, $stats)) $stats[$mounth] = 0;
            $stats[$mounth] = $stats[$mounth] + $payment['amount'];

        }

        return view('admin4', ['stats' => $stats, 'refka' => $refka]);
    }

    public function index2()
    {
        $questions = Topic::where('status', '=', 0)->get();
        $users = [];
        foreach($questions as $item) {
            $user = User::where('vk_id', '=', $item['vk_id'])->first();
            $users[$user->vk_id]['photo'] = $user->photo;
            $users[$user->vk_id]['first_name'] = $user->first_name;
            $users[$user->vk_id]['last_name'] = $user->last_name;
        }

        return view('admin2', ['questions' => $questions, 'users' => $users]);
    }

    public function index3()
    {

        $works = mywork::orderBy('id', 'desc')->take(100)->get();

        if (isset($_GET['error'])) {
            $works = mywork::orderBy('id', 'desc')->take(150)->get();



         /*
            $temp = [];
            foreach($works as $work){
                if ($work['error'] == NULL) continue;
                $temp[] = $work;
            }
            $works = $temp; */
        }

        foreach($works as &$work){
            $pids = mywork::getPids($work->id);
            $work['pids'] = implode(',', $pids);

            if ($work->percent == 100) {
                $work['live'] = false;
                continue;
            }
            $work['live'] = mywork::checkStatusWork($work->id);
        }


        return view('admin3', ['works' => $works]);
    }

    public function checkStatusWork($pid){

        $status = Process::checkStatusById($pid);

        return $status;
    }

    public function getById(Request $request)
    {
        $new_users = User::getNewUsers();
        $last_users = User::getLastUsers();
        $user = User::getById($request->input('id'));
        $questions = Topic::where('status', '=', 0)->get();
        $withdraws = Withdraw::where('status', '=', 0)->orderBy('id', 'desc')->get()->toArray();
        $tokens = Token::where('status', '=', 'free')->get()->all();
        $countTokens = count($tokens);

        if ($user)
        {
            $ref = User::getById($user['ref']);
            return view('admin', ['user' => $user, 'countTokens' => $countTokens, 'last_users' => $last_users, 'new_users' => $new_users, 'ref' => $ref, 'questions' => $questions, 'withdraws' => $withdraws, 'payments' => []]);
        }

        return redirect()->back()->with('danger', 'Пользователь не найден')->withInput();

    }

    public function store(Request $request)
    {
        $access = strtotime($request->input('access'));
        $request->merge(['access' => $access]);
        $data = $request->except(['_token']);


        $user = User::where('vk_id', '=', $request->input('vk_id'))->first();

        $user->update([
            'access' => $request->input('access'),
            'ref' => $request->input('ref'),
            'balance' => $request->input('balance'),
            'package' => $request->input('package'),
        ]);


        return redirect()->back()->with('success', 'Данные успешно обновлены');
    }

    public function getAllUsers(){

        $users = Payment::all()->map(function($name){
            return $name['user_id'];
    })->toArray();
    $users = array_count_values($users);
    foreach($users as $key => $user){
        if($user>1) echo $key .'<br>';
    }
        dd($users);
        $users = User::all()->map(function($name){
            return $name['vk_id'];
    })->toArray();

    $users = User::where('access', '>', '0')->get()->toArray();

    foreach($users as $user){
        echo $user['vk_id'] . '<br>';
    }


    }


    public function getpayments(Request $request)
    {
        $this->validate($request,[
            'time_min' => 'required',
        ]);
        $users = User::getLatest();
        $new_users = User::orderBy('id', 'desc')->take(10)->get();
        $last_users = User::where('last_seen', '>', time() - 86400)->get();
        $withdraws = Withdraw::where('status', '=', 0)->orderBy('id', 'desc')->get()->toArray();
        $questions = Topic::where('status', '=', 0)->get();
        $payments = Payment::orderBy('id', 'desc')->take(5)->get()->toArray();
        $tokens = Token::where('status', '=', 'free')->get()->all();
        $countTokens = count($tokens);

        ($request->get('time_min') == null) ? $time_min = '2019-01-01' : $time_min = $request->get('time_min');
        ($request->get('time_max') == null) ? $time_max = date('Y-m-d', time()) : $time_max = $request->get('time_max');

        $payments = Payment::whereDate('created_at', '>=', $time_min)->whereDate('created_at', '<=', $time_max)->orderBy('id', 'desc')->get();

        $data = [];
        $i = 0;
        $summ = 0;
        $refka = 0;
        foreach($payments as $payment) {
           $user = User::where('vk_id', '=', $payment['user_id'])->first();
           if (!$user) continue;
           $data[$i]['count'] = Payment::where('user_id', '=', $user['vk_id'])->count();
           $data[$i]['first_name'] = $user['first_name'];
           $data[$i]['last_name'] = $user['last_name'];
           $data[$i]['vk_id'] = $user['vk_id'];
           $data[$i]['reg'] = $user['reg'];
           $ref = null;

           if (is_numeric($user['ref'])){
                $ref = User::where('vk_id', '=', $user['ref'])->first();
                if ($ref !== null) $ref = $ref->toArray();
                $refSumm = round($payment['amount'] * (30 / 100));
                $refka += $refSumm;
                $payment['amount'] = $payment['amount'] - $refSumm;
           }
           $data[$i]['ref'] = $ref;
           $data[$i]['photo'] = $user['photo'];
           $data[$i]['amount'] = $payment['amount'];
           $data[$i]['package'] = $payment['package'];
           $data[$i]['date'] = $payment['created_at'];
           $summ += $payment['amount'];
           $utm = utm::where('vk_id', '=', $user['vk_id'])->first();
           if ($utm !== null) $utm = $utm->toArray();
           $data[$i]['utm'] = $utm;
           ++$i;
        }

        return view('admin', [
            'last_users' => $last_users,
            'new_users' => $new_users,
            'withdraws' => $withdraws,
            'questions' => $questions,
            'payments' => $data,
            'refka'    => $refka,
            'time_min' => $time_min,
            'time_max' => $time_max,
            'summ' => $summ,
            'countTokens' => $countTokens,
            'users' => $users,
    ]);

    }


    public function newPayment(Request $request)
    {
        ($request->get('id')) ? $user_id = $request->get('id') : '';
        ($request->get('vk_id'))  ? $user_id = $request->get('vk_id') : '';


        $package = $request->get('package');

        $result = User::where('vk_id', '=', $user_id)->first();

        $ref = $result->ref;

        $access = $result->access;

        if ($access < time()) {
            $access = time();
        }

        switch ($package) {
            case 1:
                $access = strtotime("+1 month", $access);
                $amount = '299';
                break;
            case 2:
                $access = strtotime("+3 month", $access);
                $amount = '499';
                break;
            case 3:
                $access = strtotime("+6 month", $access);
                $amount = '799';
                break;
            case 4:
                $access = strtotime("+12 month", $access);
                $amount = '1299';
                break;
        }

        // Проверяем, индивидуальные ли условия по рефке у человека
        if ($ref){
            $reward = round($amount * (30 / 100));
        } else
            $reward = 0;



        // Создаем оплату
        $result = Payment::create([
            'user_id' => $user_id,
            'amount' => $amount,
            'package' => $package,
            'reward' => $reward,
            'ref' => $ref,
        ]);

        // Обновляем данные пользователя
        $user = User::where('vk_id', '=', $user_id)->first();
        $user->update([
            'access' => $access,
            'package' => $package,
        ]);

        // Начисляем реферальные
        $user = User::where('vk_id', '=', $ref)->first();
        if ($user)
        $user->update([
            'balance' => $user->balance + $reward,
        ]);

        return redirect()->back()->with('Успешно');
    }


}
