<?php

namespace App\Components;

use App\Models\User;
use T4\Mvc\Application;
use T4\Mail\Sender;
use T4\Mvc\View;

class Mailer
{

    const THEMES = [
        'test' => 'Тестовое письмо',
    ];

    /**
     * @var \T4\Mail\Sender
     */
    protected $mailer;

    public function __construct()
    {
        $this->mailer = new Sender;
    }

    public function send(User $to, $action, $context = [])
    {
        $context += ['domain' => Application::getInstance()->request->protocol . '://' . $_SERVER['SERVER_NAME']];
        $view = new View('Twig', Application::getInstance()->path  . DS . 'Layouts');
        $template = 'Mail/' . implode('', array_map('ucfirst', explode('.', $action))) . '.html';
        $content = $view->render($template, $context);
        /**
         * @todo: Тема письма берется из списка actions
         */
        $this->mailer->sendMail($to->email, self::THEMES[$action], $content);
    }

}