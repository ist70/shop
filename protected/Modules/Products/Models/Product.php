<?php
/**
 * Created by PhpStorm.
 * User: Vitaliy
 * Date: 29.08.2015
 * Time: 23:17
 */

namespace App\Modules\Products\Models;


use App\Modules\Categories\Models\Category;
use T4\Core\Exception;
use T4\Dbal\QueryBuilder;
use T4\Orm\Model;
use T4\Fs\Helpers;
use T4\Http\Uploader;
use T4\Mvc\Application;

class Product
    extends Model
{
    static protected $schema = [
        'columns' => [
            'article' => ['type' => 'string', 'default' => '0000'],
            '__category_id' => ['type' => 'link'],
            'title' => ['type' => 'string'],
            'shortdescription' => ['type' => 'string'],
            'fulldescription' => ['type' => 'text'],
            'image' => ['type' => 'string'],
            'material' => ['type' => 'integer'],
            'propertysize' => ['type' => 'integer'],
            'propertycolor' => ['type' => 'integer'],
            'sellingprice' => ['type' => 'decimal'],
        ],
        'relations' => [
            'categories' => ['type' => self::BELONGS_TO, 'model' => Category::class],
        ],

    ];


    public static function findAllByCategory($categoriesList, $count = 20, $page = 1)
    {
        $categoriesList = implode(', ', $categoriesList);
        $query = new QueryBuilder();
        $query
            ->select('*')
            ->from(self::getTableName())
            ->where(' __category_id IN (' . $categoriesList . ')')
            ->order('title DESC')
            ->offset(($page - 1) * $count)
            ->limit($count);
        return self::findAllByQuery($query);
    }

    public function uploadImage($formFieldName)
    {
        $request = Application::getInstance()->request;
        if (!$request->existsFilesData() || !$request->isUploaded($formFieldName) || $request->isUploadedArray($formFieldName))
            return $this;

        try {
            $uploader = new Uploader($formFieldName);
            $uploader->setPath('/public/products/images');
            $image = $uploader();
            if ($this->image)
                $this->deleteImage();
            $this->image = $image;
        } catch (Exception $e) {
            $this->image = null;
        }
        return $this;
    }

    public function beforeSave()
    {
        if ($this->isNew()) {
            $this->published = date('Y-m-d H:i:s', time());
        }
        return true;
    }

    public function beforeDelete()
    {
        $this->deleteImage();
        return parent::beforeDelete();
    }

    public function deleteImage()
    {
        if ($this->image) {
            try {
                Helpers::removeFile(ROOT_PATH_PUBLIC . $this->image);
                $this->image = '';
            } catch (\T4\Fs\Exception $e) {
                return false;
            }
        }
        return true;
    }

} 