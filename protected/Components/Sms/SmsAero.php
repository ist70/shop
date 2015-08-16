<?php

namespace App\Components\Sms;

use T4\Mvc\Application;

class SmsAero
{
    private $gate;
    private $useraero;
    private $keyaero;
    private $from;
    private $typeanswer;

    const  ANSWER_SYSTEM = [
        'reject' => [
            'empty field' => 'Не все обязательные поля заполнены',
            'incorrect user or password' => 'Ошибка авторизации',
            'incorrect sender name' => 'Неверная (незарегистрированная) подпись отправителя',
            'incorrect destination adress' => 'Неверно задан номер телефона (формат 71234567890)',
            'incorrect date' => 'Неправильный формат даты',
            'in blacklist' => 'Телефон находится в черном списке',
        ],
        'no credits' => ['' => 'Недостаточно sms на балансе',],
    ];
    const ANSWER_STATUS_SMS = [
        'reject' => [
            'incorrect id' => 'Не все обязательные поля заполнены',
            'incorrect user or password' => 'Ошибка авторизации',
            'empty field' => 'Не все обязательные поля заполнены',
        ],

        'delivery success' => ['' => 'Сообщение доставлено',],
        'delivery failure' => ['' => 'Ошибка доставки SMS',],
        'queue' => ['' => 'Сообщение ожидает отправки',],
        'wait status' => ['' => 'Ожидание статуса сообщения',],
    ];

    function __construct()
    {
        $this->useraero = Application::getInstance()->config->smsaero->apiuser;
        $this->keyaero = md5(Application::getInstance()->config->smsaero->apikey);
        $this->typeanswer = 'json';
        $this->from = Application::getInstance()->config->smsaero->sender;
        $this->gate = Application::getInstance()->config->smsaero->apigate;
    }

// Выбрать ответ системы Smsaero

    public function getStatusSend($status, $reason = null)
    {
        return self::ANSWER_SYSTEM[$status][$reason];
    }

// Выбрать ответ статуса отправленой Sms

    public function getStatusSms($status, $reason = null)
    {
        return self::ANSWER_STATUS_SMS[$status][$reason];
    }

//  Отправить запрос на сервер

    private function sendPost($url, $data)
    {
        return file_get_contents($this->gate . $url . '?' . str_replace('+', '%20', http_build_query($data)));
    }

//  Передача сообщения

    public function sendToUser($msisdn, $text, $date = null)
    {
        return $this->sendPost('/send/',
            [
                'user' => $this->useraero,
                'password' => $this->keyaero,
                'to' => $msisdn,
                'text' => $text,
                'from' => $this->from,
                'date' => $date,
                'answer' => $this->typeanswer,
            ]
        );
    }

//  Проверка состояния отправленного сообщения

    public function getStatus($id)
    {
        return $this->sendPost('/status/',
            [
                'user' => $this->useraero,
                'password' => $this->keyaero,
                'id' => $id,
                'answer' => $this->typeanswer,
            ]
        );
    }

//  Проверка состояния счёта

    public function getBalance()
    {
        return $this->sendPost('/balance/',
            [
                'user' => $this->useraero,
                'password' => $this->keyaero,
                'answer' => $this->typeanswer,
            ]
        );
    }

//  Список доступных подписей отправителя

    public function getSenders()
    {
        return $this->sendPost('/senders/',
            [
                'user' => $this->useraero,
                'password' => $this->keyaero,
                'answer' => $this->typeanswer,
            ]
        );
    }
}