<?php


namespace istvan0304\imagemanager\models;


use yii\base\Model;

class CkImageForm extends Model
{
    public $img_files;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['img_files'], 'file', 'extensions' => 'jpg, jpeg, png', 'maxSize' => 3000000, 'maxFiles' => 10],
        ];
    }
}
