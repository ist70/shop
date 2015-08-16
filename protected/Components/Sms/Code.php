<?php

namespace App\Components\Sms;

use App\Models\User;
use T4\Core\Session;

class Code
{

    public static function genCodeConfirm(User $user)
    {
 /*       $stringData = 'abcdefghijkmnpqrstuvwxyzABCDEFGHGKLMNPQRSTUVWXYZ1234567890';
        $stringCount=strlen($stringData);
        $code='';
        for($i=0;$i<6; $i++){
            $code.=$stringData[rand(0,$stringCount)];
        }
        self::setCodeConfirm($user, $code);
        return $code;*/

        $code = str_replace([o,l], 0, base_convert(mt_rand(300000, time()), 10, 36));
        self::setCodeConfirm($user, $code);
        return $code;
    }

    private static function setCodeConfirm(User $user, $code)
    {
        Session::init();
        Session::set($user->msisdn, $code);
    }

    public static function getCodeConfirm(User $user)
    {
       return Session::get($user->msisdn);
    }
} 