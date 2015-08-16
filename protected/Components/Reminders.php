<?php

namespace App\Components;

use T4\Core\Session;
use T4\Mvc\Application;
use T4\Core\Std;

class Reminders
    extends Std
{

    public function set($reminder, $type, $text)
    {
        $app = Application::getInstance();
        $flash = $app->flash->getData();

        $reminders = isset($flash['reminders']) ? $flash['reminders'] : new Std();

        if (!empty(Session::get('dismiss_reminder_' . $reminder))) {
            unset($reminders->{$reminder});
            $app->flash->reminders = $reminders;
            return $this;
        }

        $reminders->{$reminder} = new Std([
            'type' => $type,
            'text' => trim($text),
        ]);
        $app->flash->reminders = $reminders;
        return $this;
    }

    public function remove($reminder) {
        $app = Application::getInstance();
        $flash = $app->flash->getData();
        $reminders = isset($flash['reminders']) ? $flash['reminders'] : new Std();
        unset($reminders->{$reminder});
        $app->flash->reminders = $reminders;
        return $this;
    }

}