<?php
/**
 * Created by PhpStorm.
 * User: 126
 * Date: 06.05.2015
 * Time: 11:28
 */

namespace App\Components\Video;


use App\Modules\Learning\Models\Video;

interface IProvider
{
    public function __construct(Video $model);

    public function sanitizeId($value);

    public function getDuration();

    public function getPlayer();
}