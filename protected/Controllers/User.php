<?php

namespace App\Controllers;

use App\Components\Auth\Identity;
use App\Components\Controller;
use App\Components\Reminders;
use App\Components\Sms\Code;
use App\Components\Sms\SmsAero;
use App\Models\City;
use App\Models\Interest;
use T4\Core\Collection;
use T4\Core\Exception;
use T4\Fs\Helpers;
use T4\Http\Uploader;

class User
    extends Controller
{

    public function access($action)
    {
        switch ($action) {
            case 'Avatar':
            case 'LoadAvatar':
            case 'SetAvatar':
            case 'Interests':
            case 'Msisdn':
                return !empty($this->app->user);
        }
        return true;
    }

    public function actionDefault()
    {
        if (empty($this->app->user)) {
            $this->app->flash->message = 'Зарегистируйтесь, чтобы стать студентом Академии программирования и получить доступ ко всем возможностям образовательной платформы &laquo;ProfIT&raquo;';
            $this->redirect('/learner/register.html');
        }
    }

    public function actionAvatar()
    {
    }

    public function actionLoadAvatar()
    {
        if ($this->app->request->isUploaded('avatar')) {
            try {
                $uploader = new Uploader('avatar');
                $uploader->setPath('/public/tmp');
                $file = $uploader();
                if (false === $file) {
                    throw new Exception('Не удалось загрузить файл');
                }
                $this->data->result = true;
                $this->data->image = $file;
                return;
            } catch (Exception $e) {
                $this->data->result = false;
                $this->data->error = $e->getMessage();
                return;
            }
        }
        $this->data->result = false;
        $this->data->error = 'Файл не был загружен';
    }

    public function actionSetAvatar($img, $width, $cropX, $cropY, $cropW, $cropH)
    {
        $imgRes = imagecreatefromstring(@file_get_contents(ROOT_PATH_PUBLIC . $img));
        if (false === $imgRes) {
            $this->data->result = false;
            $this->data->error = 'Исходный файл не существует';
            return;
        }
        $imgType = @exif_imagetype(ROOT_PATH_PUBLIC . $img);
        if (false === $imgType) {
            $this->data->result = false;
            $this->data->error = 'Неподдерживаемый тип файла';
            return;
        }
        $imgW = getimagesize(ROOT_PATH_PUBLIC . $img)[0];

        $ratio = $width / $imgW;
        $cropX = round($cropX / $ratio);
        $cropY = round($cropY / $ratio);
        $cropW = round($cropW / $ratio);
        $cropH = round($cropH / $ratio);

        $newImg = imagecrop($imgRes, ['x' => $cropX, 'y' => $cropY, 'width' => $cropW, 'height' => $cropH]);
        if (!is_dir(ROOT_PATH_PUBLIC . '/public/avatars/')) {
            Helpers::mkDir(dirname(ROOT_PATH_PUBLIC . '/public/avatars/'));
        }
        switch ($imgType) {
            case IMAGETYPE_PNG:
                $fileName = '/public/avatars/' . md5($img . $this->app->user->getPk()) . '.png';
                imagepng($newImg, ROOT_PATH_PUBLIC . $fileName);
                break;
            case IMAGETYPE_JPEG:
                $fileName = '/public/avatars/' . md5($img . $this->app->user->getPk()) . '.jpg';
                imagejpeg($newImg, ROOT_PATH_PUBLIC . $fileName);
                break;
            case IMAGETYPE_GIF:
                $fileName = '/public/avatars/' . md5($img . $this->app->user->getPk()) . '.gif';
                imagejpeg($newImg, ROOT_PATH_PUBLIC . $fileName);
                break;
        }

        try {
            $this->app->user->deleteImage();
            $this->app->user->avatar = $fileName;
            $this->app->user->save();
            Helpers::removeFile(ROOT_PATH_PUBLIC . $img);
        } catch (Exception $e) {
        }

        $this->data->result = true;
    }

    public function actionLogin($login = null)
    {
        if (null !== $login) {
            try {
                $identity = new Identity();
                $user = $identity->authenticate($login);
                $this->app->flash->message = 'Добро пожаловать, ' . $user->email . '!';
                $this->redirect('/');
            } catch (\App\Components\Auth\MultiException $e) {
                $this->data->errors = $e;
            }
            $this->data->email = $login->email;
        }
    }

    public function actionLogout()
    {
        $identity = new Identity();
        $identity->logout();
        $this->redirect('/');
    }

    public function actionRegister($register = null)
    {
        if (null !== $register) {
            try {

                if (!empty($this->app->request->post->msisdn)) {
                    $register->msisdn = $this->app->request->post->countryCode . $this->app->request->post->msisdn;
                }

                $identity = new Identity();
                $user = $identity->register($register);
                $identity->login($user);

                $this->app->flash->message = 'Добро пожаловать, ' . $user->email . '!';
                $this->redirect('/');

            } catch (\App\Components\Auth\MultiException $e) {
                $this->data->errors = $e;
            }

            $this->data->email = $register->email;
            $this->data->firstName = $register->firstName;
            $this->data->lastName = $register->lastName;
            $this->data->district = $register->district;
            $this->data->city = City::findByColumn('__district_id', $register->district, ['where' => 'isCenter=1']);
            $this->data->msisdn = $this->app->request->post->msisdn;

        }
    }

    public function actionInterests($interests = null, $do = false)
    {
        if ($do) {
            $i = new Collection();
            if (null !== $interests) {
                foreach ($interests as $id) {
                    $interest = Interest::findByPK($id);
                    if (!empty($interest)) {
                        $i[] = $interest;
                    }
                }
            }
            $this->app->user->setInterests($i)->save();
            (new Reminders())->remove('emptyInterests');
        }

        $this->data->interests = Interest::findAllTree();
    }

    public function actionMsisdn($countryCode = null, $msisdn = null, $codeconfirmation = null, $do = false)
    {
        $this->data->notconfirm = false;
        if ($do) {
            if (Code::getCodeConfirm($this->app->user) === $codeconfirmation) {
                if (!empty($countryCode) && !empty($msisdn)) {
                    $this->app->user->msisdn = $countryCode . $msisdn;
                    $this->app->user->save();
                    (new Reminders())->remove('emptyMsisdn');
                }
            } else {
                $this->data->notconfirm = true;
            }
        }
        $this->data->msisdn = $this->app->user->getMsisdnWoCountryCode();
    }

    public function actionSendSms($countryCode = null, $msisdn = null)
    {
        $sms = new SmsAero();
        $response = json_decode($sms->sendToUser($countryCode[1] . $msisdn, 'Код подтверждения: ' .
            Code::genCodeConfirm($this->app->user)));
        if ($response->result !== 'accepted') {
            $this->data->result = false;
            $this->data->status = $sms->getStatusSend($response->result, $response->reason);
            return;
        }
        $status = json_decode($sms->getStatus($response->id));
        if ($status->result !== 'delivery success' || $status->result !== 'queue') {
            $this->data->result = false;
            $this->data->status = $sms->getStatusSms($response->result, $response->reason);
            return;
        }
        $this->data->result = true;
    }
}