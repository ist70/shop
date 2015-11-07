<?php
/**
 * Created by PhpStorm.
 * User: Vitaliy
 * Date: 29.08.2015
 * Time: 23:04
 */

namespace App\Modules\Categories\Models;


use App\Modules\Products\Models\Product;
use T4\Orm\Model;

class Category
    extends Model
{
    static protected $schema = [
        'table' => 'categories',
        'columns' => [
            'title' => ['type' => 'string', 'length' => '100'],
        ],
    ];

    static protected $extensions = ['tree'];

}