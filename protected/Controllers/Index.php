<?php

namespace App\Controllers;

use T4\Mvc\Controller;
use App\Components\Import;


class Index
    extends Controller
{

    public function actionDefault()
    {
    }

    public function action404()
    {
    }

    public function actionCaptcha($config = null)
    {
        if (null !== $config) {
            $config = $this->app->config->extensions->captcha->$config;
        }
        $this->app->extensions->captcha->generateImage($config);
        die;
    }

    public function actionImport()
    {
        $xml = new Import();
        $xml->importXmlData(__DIR__ . '/../../Catalog.xml'); die;
    }

}
