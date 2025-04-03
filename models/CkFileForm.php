<?php

namespace istvan0304\ckfilemanager\models;

use yii\base\Model;

/**
 * Class CkFileForm
 * @package istvan0304\ckfilemanager\models
 */
class CkFileForm extends Model
{
    const MAX_SIZE = 30 * 1024 *1024;
    const MAX_FILE_COUNT = 12;
    public $uploaded_files;

    public $dynamic_path;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['uploaded_files'], 'file', 'extensions' => 'jpg, jpeg, png', 'maxSize' => self::MAX_SIZE, 'maxFiles' => self::MAX_FILE_COUNT],
            [['dynamic_path'], 'safe'],
        ];
    }
}
