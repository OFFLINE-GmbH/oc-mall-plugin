<?php
return [
    "admin" => [
        "checkout_failed" => [
            "error" => "Сообщение об ошибке",
            "error_details" => "Детали ошибки",
            "not_processed" => "Следующий заказ не может быть обработан правильно. Возможно, вам придется связаться с клиентом.",
            "payment_id" => "Идентификатор платежа",
            "subject" => "Не удалось оформить заказ #: номер"
        ],
        "checkout_succeeded" => [
            "order_placed" => "В вашем магазине был сделан следующий заказ:",
            "subject" => "Новый заказ №: номер"
        ]
    ],
    "checkout" => [
        "failed" => [
            "check_order_status" => "Для проверки статуса вашего заказа вы можете в любое время зайти в наш магазин.",
            "error" => "Сообщение об ошибке",
            "payment_id" => "Идентификатор платежа",
            "problem_message" => "Мы очень сожалеем, что во время оформления заказа возникла проблема. Мы рассмотрим проблему и свяжемся с вами для получения дополнительной информации.",
            "subject" => "Ошибка оформления заказа №: номер"
        ],
        "succeeded" => [
            "check_order_status" => "Вы можете проверить статус вашего заказа, посетив раздел аккаунта нашего магазина.",
            "subject" => "Подтверждение заказа №: номер",
            "thanks_for_order" => "Большое спасибо за ваш заказ. Мы получили следующую информацию"
        ]
    ],
    "common" => [
        "hello" => "Привет: имя",
        "order_details" => "Информация для заказа",
        "view_order_in_backend" => "Посмотреть заказ в магазине",
        "view_order_status_online" => "Посмотреть статус заказа онлайн"
    ],
    "customer" => [
        "created" => [
            "button" => [
                "confirm" => "Подтвердите Ваш электронный адрес",
                "visit_store" => "Посетите наш магазин"
            ],
            "confirm_mail" => "Добро пожаловать в наш магазин! Пожалуйста, нажмите на кнопку ниже, чтобы подтвердить свой адрес электронной почты.",
            "message" => "Добро пожаловать в наш магазин! Вы можете войти, используя свой адрес электронной почты **: электронная почта ** и начать покупки сразу.",
            "possibilities" => "Ваша учетная запись позволяет вам отслеживать открытые и прошлые заказы.",
            "subject" => "Добро пожаловать в наш магазин,: имя"
        ]
    ],
    "order" => [
        "partials" => [
            "billing_address" => "Платежный адрес",
            "billing_and_shipping" => "Биллинг и адрес доставки",
            "currently_pending" => "Платеж по этому заказу в настоящее время ожидает рассмотрения.",
            "has_been_paid_on" => "Заказ был оплачен на",
            "shipping_address" => "Адреса доставки",
            "track_shipping_status" => "Вы можете отслеживать статус доставки вашего заказа со следующей информацией:"
        ],
        "shipped" => [
            "message" => "Ваш заказ ** #: номер ** был отправлен.",
            "subject" => "Ваш заказ был отправлен"
        ],
        "state_changed" => [
            "message" => "Мы просто хотели, чтобы вы знали, что ваш заказ ** #: номер ** обновлен до нового статуса: **: состояние **",
            "subject" => "Статус вашего заказа изменился"
        ]
    ],
    "payment" => [
        "failed" => [
            "message" => "Мы просто хотели, чтобы вы знали, что оплата заказа ** #: номер ** не удалась",
            "payment_advice" => "Пожалуйста, войдите в свою учетную запись и попробуйте снова оплатить заказ.",
            "subject" => "Платеж за ваш заказ не прошел",
            "support" => "Если вы продолжаете испытывать проблемы с платежами, пожалуйста, свяжитесь с нами."
        ],
        "paid" => [
            "message" => "Мы только что получили оплату за ваш заказ ** #: номер **.",
            "process_order" => "Теперь мы начнем дальнейшую обработку заказа.",
            "subject" => "Спасибо вам за ваш платеж"
        ],
        "refunded" => [
            "duration" => "Помните, что получение средств может занять несколько дней.",
            "message" => "Мы только что вернули оплату за ваш заказ ** #: номер **.",
            "subject" => "Ваш платеж был возвращен"
        ]
    ]
];
