<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Etod Graphic store configuration
    |--------------------------------------------------------------------------
    | Drivers stay on "log"/"mock" for development. Real SMS.ir, WhatsApp
    | Cloud and Zarinpal adapters read their credentials from here later,
    | through environment variables only.
    */

    'currency' => env('ETOD_CURRENCY', 'IRR'),

    'payment_driver' => env('ETOD_PAYMENT_DRIVER', 'mock'),

    'sms_driver' => env('ETOD_SMS_DRIVER', 'log'),

    'whatsapp_driver' => env('ETOD_WHATSAPP_DRIVER', 'log'),

];
