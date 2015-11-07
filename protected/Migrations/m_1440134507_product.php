<?php

namespace App\Migrations;

use T4\Dbal\Drivers\Mysql;
use T4\Dbal\QueryBuilder;
use T4\Orm\Migration;

class m_1440134507_product
    extends Migration
{

    public function up()
    {
        $this->createTable('products', [
            'article' => ['type' => 'string', 'default' => '0000'],
            '__category_id' => ['type'=>'link'],
            'title' => ['type' => 'string'],
            'shortdescription' => ['type' => 'string'],
            'fulldescription' => ['type' => 'text'],
            'image' => ['type' => 'string'],
            'material' => ['type' => 'integer'],
            'propertysize' => ['type' => 'integer'],
            'propertycolor' => ['type' => 'integer'],
            'sellingprice' => ['type' => 'decimal'],

        ], [
            ['type' => 'unique', 'columns' => ['article']],
        ],
            [ ]);

        $this->createTable('categories', [
                'title' => ['type' => 'string', 'length' => '100'],
            ],
            [],
            ['tree']);
    }

    public function down()
    {
        $this->dropTable('products');
        $this->dropTable('categories');
    }

}