<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use YooKassa\Client;

class PaymentController extends Controller
{
    public function index()
    {
        $client = new Client();
        $client->setAuth('377186', 'live_BsZ2oqf1gzmRHDc-3P2Yo1aRVQiMgLC2Ap6IYY4AyuI');

        try {
            $idempotenceKey = uniqid('', true);

            $response = $client->createPayment(
                [
                    'amount' => [
                        'value' => '30.00',
                        'currency' => 'RUB',
                    ],
                    'confirmation' => [
                        'type' => 'redirect',
                        'locale' => 'ru_RU',
                        'return_url' => '/return_url',
                    ],
                    'capture' => true,
                    'description' => 'Заказ №72',
                    'metadata' => [
                        'orderNumber' => 1006
                    ],
                    'receipt' => [
                        'customer' => [
                            'full_name' => 'Ivanov Ivan Ivanovich',
                            'email' => 'email@email.ru',
                            'phone' => '79991721767',
                            'inn' => '6321341814'
                        ],
                        'items' => [
                            [
                                'description' => 'Переносное зарядное устройство Хувей',
                                'quantity' => '1.00',
                                'amount' => [
                                    'value' => 30,
                                    'currency' => 'RUB'
                                ],
                                'vat_code' => '2',
                                'payment_mode' => 'full_payment',
                                'payment_subject' => 'commodity',
                                'country_of_origin_code' => 'CN',
                                'product_code' => '44 4D 01 00 21 FA 41 00 23 05 41 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 12 00 AB 00',
                                'customs_declaration_number' => '10714040/140917/0090376',
                                'excise' => '20.00',
                                'supplier' => [
                                    'name' => 'andrey',
                                    'phone' => '79991721767',
                                    'inn' => '6321341814'
                                ]
                            ],
                        ]
                    ]
                ],
                $idempotenceKey
            );

            //получаем confirmationUrl для дальнейшего редиректа
            $confirmationUrl = $response->getConfirmation()->getConfirmationUrl();

            echo $confirmationUrl; die;

            $confirmationToken= $response->getConfirmation()->getConfirmationToken();

            return view('payment.form', ['token' => $confirmationToken]);

        } catch (\Exception $e) {
            $response = $e;
            var_dump($e->getMessage()); die;
        }

        if (!empty($response)) {
            echo '<pre>';
            var_dump($confirmationUrl, $response); die;
        }

        return $confirmationToken;
    }

    public function form()
    {
        return view('payment.form');
    }
}


