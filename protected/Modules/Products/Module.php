<?php
/**
 * Created by PhpStorm.
 * User: Vitaliy
 * Date: 29.08.2015
 * Time: 22:50
 */

namespace App\Modules\Products;


class Module
    extends \App\Components\Module
{

    public function getAdminMenu()
    {
        return [
            ['title' => 'Продукция',
             'icon' => '<i class="glyphicon glyphicon-menu-hamburger"></i>',
             'url' => '/admin/products/'],
        ];
    }

} 