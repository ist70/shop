<?php

namespace App\Modules\Products\Controllers;


use App\Components\Admin\Controller;
use App\Modules\Products\Models\Product;
use T4\Http\E404Exception;

class Index
    extends Controller

{
    const DEFAULT_PRODUCT_COUNT = 20;

    public function actionDefault($count=self::DEFAULT_PRODUCT_COUNT)
    {
        $this->data->products = Product::findAll(
            [
                'order' => 'title DESC',
                'limit' => $count,
            ]
        );
    }

    public function actionOne($id)
    {
        $this->data->item = Product::findByPK($id);
        if (empty($this->data->item)) {
            throw new E404Exception('Товар не найден');
        }
    }

}