<?php

    $orderProcessData = [
        [
            'icon'        => asset('public/build/images/index/order-process/new-order.svg'),
            'title'       => translate('home_ff_order_process_first_title'),
            'description' => translate('home_ff_order_process_first_desc'),
        ],
        [
            'icon'        => asset('public/build/images/index/order-process/purchase-order.svg'),
            'title'       => translate('home_ff_order_process_second_title'),
            'description' => translate('home_ff_order_process_second_desc'),
        ],
        [
            'icon'        => asset('public/build/images/index/order-process/invoice-sent.svg'),
            'title'       => translate('home_ff_order_process_third_title'),
            'description' => translate('home_ff_order_process_third_desc'),
        ],
        [
            'icon'        => asset('public/build/images/index/order-process/payment-confirmed.svg'),
            'title'       => translate('home_ff_order_process_fourth_title'),
            'description' => translate('home_ff_order_process_fourth_desc'),
        ],
        [
            'icon'        => asset('public/build/images/index/order-process/shipping.svg'),
            'title'       => translate('home_ff_order_process_fifth_title'),
            'description' => translate('home_ff_order_process_fifth_desc'),
        ],
        [
            'icon'        => asset('public/build/images/index/order-process/order-completed.svg'),
            'title'       => translate('home_ff_order_process_sixth_title'),
            'description' => translate('home_ff_order_process_sixth_desc'),
        ],
    ];

    views('new/home/components/order_process_view', ['orderProcessData' => $orderProcessData]);
