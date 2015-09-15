<?php
/**
 * Created by PhpStorm.
 * User: Vitaliy
 * Date: 29.08.2015
 * Time: 22:56
 */

namespace App\Modules\Categories\Controllers;


use App\Modules\Categories\Models\Category;
use T4\Core\Exception;
use App\Components\Admin\Controller;

class Admin
    extends Controller
{

    public function actionDefault()
    {
        $this->data->items = Category::findAllTree();
    }

    public function actionEdit($id = null, $parent = null)
    {
        if (null === $id || 'new' == $id) {
            $this->data->item = new Category();
            if (null !== $parent) {
                $this->data->item->parent = $parent;
            }
        } else {
            $this->data->item = Category::findByPK($id);
        }
    }

    public function actionSave()
    {
        if (!empty($this->app->request->post->id)) {
            $item = Category::findByPK($this->app->request->post->id);
        } else {
            $item = new Category();
        }
        $item
            ->fill($this->app->request->post)
            ->save();
        $this->redirect('/admin/categories/');
    }

    public function actionDelete($id)
    {
        $item = Category::findByPK($id);
        if ($item)
            $item->delete();
        $this->redirect('/admin/categories/');
    }

    public function actionUp($id)
    {
        $item = Category::findByPK($id);
        if (empty($item))
            $this->redirect('/menu/admin/');
        $sibling = $item->getPrevSibling();
        if (!empty($sibling)) {
            $item->insertBefore($sibling);
        }
        $this->redirect('/admin/categories/');
    }

    public function actionDown($id)
    {
        $item = Category::findByPK($id);
        if (empty($item))
            $this->redirect('/menu/admin/');
        $sibling = $item->getNextSibling();
        if (!empty($sibling)) {
            $item->insertAfter($sibling);
        }
        $this->redirect('/admin/categories/');
    }

    public function actionMoveBefore($id, $to)
    {
        try {
            $item = Category::findByPK($id);
            if (empty($item)) {
                throw new Exception('Source element does not exist');
            }
            $destination = Category::findByPK($to);
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

    public function actionMoveAfter($id, $to)
    {
        try {
            $item = Category::findByPK($id);
            if (empty($item)) {
                throw new Exception('Source element does not exist');
            }
            $destination = Category::findByPK($to);
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