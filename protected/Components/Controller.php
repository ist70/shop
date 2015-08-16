<?php

namespace App\Components;

class Controller
    extends \T4\Mvc\Controller
{

    /**
     * @var \App\Components\Mailer
     */
    protected $mailer;

    protected function beforeAction($action)
    {
        $this->mailer = new Mailer();

        if (!empty($this->app->user)) {

            $user = $this->app->user;
            $reminder = new Reminders();

            if ($user->hasRole('learner') && $user->interests->isEmpty()) {
                $reminder->set(
                    'emptyInterests',
                    'info',
                    '
                        Вы не заполнили данные о своих интересах.
                        <a href="/user/interests" class="alert-link">Заполните сейчас</a>
                        - это поможет предложить вам наиболее интересные для вас курсы и программы!
                    '
                );
            }

            if ($user->hasRole('learner') && empty($user->msisdn)) {
                $reminder->set(
                    'emptyMsisdn',
                    'info',
                    '
                        Вы не указали свой номер телефона.
                        <a href="/user/msisdn" class="alert-link">Укажите его сейчас</a>
                        и бесплатно получайте sms-оповещения о важных событиях - консультациях по курсам, оценках за домашние задания,
                        ответах преподавателей на ваши вопросы.
                    '
                );
            }

        }

        return parent::beforeAction($action);
    }

}