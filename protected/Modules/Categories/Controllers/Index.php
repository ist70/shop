<?php
/**
 * Created by PhpStorm.
 * User: Vitaliy
 * Date: 30.08.2015
 * Time: 10:44
 */

namespace App\Modules\Categories\Controllers;


use App\Components\Admin\Controller;
use App\Modules\Categories\Models\Category;
use App\Modules\Products\Models\Product;
use T4\Core\Collection;
use T4\Http\E404Exception;

class Index
    extends Controller

{
    const DEFAULT_PRODUCT_COUNT = 20;

    public function actionDefault()
    {
        $this->data->items = Category::findAllTree();
        $this->data->products = Product::findAll();
    }

    public function actionProductsByCategory($id, $count = self::DEFAULT_PRODUCT_COUNT, $color = 'default')
    {
        $categoriesList = [];
        $this->data->category = Category::findByPK($id);
        if (empty($this->data->category)) {
            throw new E404Exception;
        }
        $this->data->childs = $this->data->category->findAllChildren();
        $this->data->parents = $this->data->category->findAllParents();

        if (!empty($this->data->childs[0])) {
            $categoriesList = $this->data->childs->collect('Pk');
        } else {
            $categoriesList[] = $this->data->category->getPk();
        }
        $this->data->page = $this->app->request->get->page ?: 1;
        $this->data->items = Product::findAllByCategory($categoriesList, $count, $this->data->page);
        $this->data->total = count($this->data->items);
        $this->data->size = $count;
        $this->data->color = $color;
        $this->view->meta->title = $this->data->category->title;
    }
} 