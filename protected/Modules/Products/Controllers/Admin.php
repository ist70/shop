<?php
/**
 * Created by PhpStorm.
 * User: Vitaliy
 * Date: 31.08.2015
 * Time: 12:01
 */

namespace App\Modules\Products\Controllers;


use App\Components\Admin\Controller;
use App\Modules\Products\Models\Product;
use T4\Core\Exception;

class Admin
    extends Controller
{

    const PAGE_SIZE = 20;

    public function actionDefault($page = 1)
    {
        $this->data->itemsCount = Product::countAll();
        $this->data->pageSize = self::PAGE_SIZE;
        $this->data->activePage = $page;

        $this->data->items = Product::findAll([
            'order' => '__category_id',
            'offset'=> ($page-1)*self::PAGE_SIZE,
            'limit'=> self::PAGE_SIZE
        ]);
    }

    public function actionView($id)
    {
        $this->data->id = $id;
    }

    public function actionEdit($id = null)
    {
        if (null === $id || 'new' == $id) {
            $this->data->item = new Product();
        } else {
            $this->data->item = Product::findByPK($id);
        }
    }

    public function actionSave()
    {
        if (!empty($this->app->request->post->id)) {
            $item = Product::findByPK($this->app->request->post->id);
        } else {
            $item = new Product();
        }
        $item->fill($this->app->request->post);
        $item
            ->uploadImage('image')
            ->save();
        $this->redirect('/admin/products/');
    }

    public function actionDelete($id)
    {
        $item = Product::findByPK($id);
        $item->delete();
        $this->redirect('/admin/products/');
    }

    public function actionDeleteImage($id)
    {
        $item = Product::findByPK($id);
        if ($item) {
            $item->deleteImage();
            $item->save();
            $this->data->result = true;
        } else {
            $this->data->result = false;
        }
    }

    /**
     * TOPICS
     */

    public function actionTopics()
    {
        $this->data->items = Topic::findAllTree();
    }

    public function actionTopicEdit($id = null)
    {
        if (null === $id || 'new' == $id) {
            $this->data->item = new Topic();
        } else {
            $this->data->item = Topic::findByPK($id);
        }
    }

    public function actionTopicSave()
    {
        if (!empty($this->app->request->post->id)) {
            $item = Topic::findByPK($this->app->request->post->id);
        } else {
            $item = new Topic();
        }
        $item->fill($this->app->request->post);
        $item->save();
        $this->redirect('/admin/news/topics/');
    }

    public function actionTopicDelete($id)
    {
        $item = Topic::findByPK($id);
        if ($item) {
            $item->delete();
        }
        $this->redirect('/admin/news/topics/');
    }

    public function actionTopicUp($id)
    {
        $item = Topic::findByPK($id);
        if (empty($item))
            $this->redirect('/admin/news/topics');
        $sibling = $item->getPrevSibling();
        if (!empty($sibling)) {
            $item->insertBefore($sibling);
        }
        $this->redirect('/admin/news/topics');
    }

    public function actionTopicDown($id)
    {
        $item = Topic::findByPK($id);
        if (empty($item))
            $this->redirect('/admin/news/topics');
        $sibling = $item->getNextSibling();
        if (!empty($sibling)) {
            $item->insertAfter($sibling);
        }
        $this->redirect('/admin/news/topics');
    }

    public function actionTopicMoveBefore($id, $to)
    {
        try {
            $item = Topic::findByPK($id);
            if (empty($item)) {
                throw new Exception('Source element does not exist');
            }
            $destination = Topic::findByPK($to);
            if (empty($destination)) {
                throw new Exception('Destination element does not exist');
            }
            $item->insertBefore($destination);
            $this->data->result = true;
        } catch (Exception $e) {
            $this->data->result = false;
            $this->data->error = $e->getMessage();
        }
    }

    public function actionTopicMoveAfter($id, $to)
    {
        try {
            $item = Topic::findByPK($id);
            if (empty($item)) {
                throw new Exception('Source element does not exist');
            }
            $destination = Topic::findByPK($to);
            if (empty($destination)) {
                throw new Exception('Destination element does not exist');
            }
            $item->insertAfter($destination);
            $this->data->result = true;
        } catch (Exception $e) {
            $this->data->result = false;
            $this->data->error = $e->getMessage();
        }
    }
} 