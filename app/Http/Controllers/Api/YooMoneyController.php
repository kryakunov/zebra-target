<?php

namespace App\Http\Controllers\Api;

use YooKassa\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class YooMoneyController extends Controller
{
    const SHOP_ID = 377186;
    const SECRET_KEY = 'live_-CKsBGgfw5Zas4oYVSk-VbSn3r6pVYzu7gSN4984R8I';

    public function index()
    {
        $client = new Client();
        $client->setAuth(self::SHOP_ID, self::SECRET_KEY);

                
        try {
            $idempotenceKey = uniqid('', true);
            $response = $client->createPayment(
                [
                    'amount' => [
                        'value' => '10.00',
                        'currency' => 'RUB',
                    ],
                    'confirmation' => [
                        'type' => 'redirect',
                        'locale' => 'ru_RU',
                        'return_url' => 'https://zebra-target.ru',
                    ],
                    'capture' => true,
                    'description' => 'Заказ №72',
                    'metadata' => [
                        'orderNumber' => 1001
                    ],
                    'receipt' => [
                        'customer' => [
                            'full_name' => 'Ivanov Ivan Ivanovich',
                            'email' => 'email@email.ru',
                            'phone' => '79211234567',
                        ],
                        'items' => [
                            [
                                'description' => 'Переносное зарядное устройство Хувей',
                                'quantity' => '1.00',
                                'amount' => [
                                    'value' => 10,
                                    'currency' => 'RUB'
                                ],
                                'vat_code' => '2',
                                'payment_mode' => 'full_payment',
                                'payment_subject' => 'commodity',
                                'country_of_origin_code' => 'CN',
                                'product_code' => '44 4D 01 00 21 FA 41 00 23 05 41 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 12 00 AB 00',
                                'customs_declaration_number' => '10714040/140917/0090376',
                                'excise' => '20.00',
        
                            ],
                        ]
                    ]
                ],
                $idempotenceKey
            );
            
            //получаем confirmationUrl для дальнейшего редиректа
            $confirmationUrl = $response->getConfirmation()->getConfirmationUrl();

            return redirect($confirmationUrl);

        } catch (\Exception $e) {
            $response = $e;
            dd($e->getMessage());
        }

    }

    public function success(Request $request)
    {
        file_put_contents('yoo.txt', json_encode($request->all()));
    }
}
