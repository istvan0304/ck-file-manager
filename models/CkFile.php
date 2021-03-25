<?php


namespace istvan0304\ckfilemanager\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "ckfile".
 *
 * @property int $id Id
 * @property string $file_name
 * @property string $orig_name
 * @property string $file_hash
 * @property string $mime
 * @property string $extension
 * @property int $size
 * @property string $create_time Létrehozás dátuma
 * @property string $update_time Módosítás dátuma
 */
class CkFile extends ActiveRecord
{
    const THUMBNAIL = 'thumbnail_';
    const THUMBNAIL_DIRECTORY = '.thumbnails';
    const THUMBNAIL_WIDTH = 130;
    const THUMBNAIL_HEIGHT = 130;
    const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];
    const OTHER_FILE_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png'];
    const MAX_FILE_COUNT = 1;

    const SCENARIO_IMAGE = 'image';
    const SCENARIO_OTHER_FILE = 'other-file';

    const TYPE_IMAGE = 1;           // Image file
    const TYPE_OTHER_FILE = 2;      // Not image file

    public $uploaded_file;
    public $thumbnail;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ckfile';
    }

    /**
     * @param bool $event
     * @return bool
     */
    public function beforeSave($event)
    {
        if (parent::beforeSave($event)) {
            if ($this->isNewRecord) {
                $this->create_time = new Expression('NOW()');
            } else {
                $this->update_time = new Expression('NOW()');
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['uploaded_file'], 'file', 'extensions' => implode(', ', self::IMAGE_EXTENSIONS), 'maxSize' => Yii::$app->ckfilemanager->maxImageFileSizeUpload, 'maxFiles' => self::MAX_FILE_COUNT, 'on' => self::SCENARIO_IMAGE],
            [['uploaded_file'], 'file', 'extensions' => implode(', ', self::OTHER_FILE_EXTENSIONS), 'maxSize' => Yii::$app->ckfilemanager->maxFileSizeUpload, 'maxFiles' => self::MAX_FILE_COUNT, 'on' => self::SCENARIO_OTHER_FILE],
            [['file_name', 'orig_name', 'file_hash', 'type'], 'required'],
            [['size'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['file_name', 'orig_name', 'file_hash', 'mime'], 'string', 'max' => 255],
            [['extension'], 'string', 'max' => 32],
            [['file_hash'], 'validateFileHash']
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_IMAGE] = ['id', 'uploaded_file', 'thumbnail', 'file_name', 'orig_name', 'file_hash', 'mime', 'extension', 'size', 'type', 'create_time', 'update_time'];
        $scenarios[self::SCENARIO_OTHER_FILE] = ['id', 'file_name', 'orig_name', 'file_hash', 'mime', 'extension', 'size', 'type', 'create_time', 'update_time'];
        return $scenarios;
    }

    /**
     * @param $attribute
     * @return bool
     */
    public function validateFileHash($attribute)
    {
        if (!Yii::$app->ckfilemanager->allowDuplicateFile) {
            $fileCount = self::find()->where(['file_hash' => $this->file_hash])->count();

            if ($fileCount > 0) {
                $this->addError($attribute, Yii::t('ckfile', 'This file is already uploaded!'));
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('ckfile', 'ID'),
            'file_name' => Yii::t('ckfile', 'File Name'),
            'orig_name' => Yii::t('ckfile', 'Orig Name'),
            'file_hash' => Yii::t('ckfile', 'File Hash'),
            'mime' => Yii::t('ckfile', 'Mime'),
            'extension' => Yii::t('ckfile', 'Extension'),
            'size' => Yii::t('ckfile', 'Size'),
            'type' => Yii::t('ckfile', 'Type'),
            'create_time' => Yii::t('ckfile', 'Cr Date'),
            'update_time' => Yii::t('ckfile', 'Mod Date'),
        ];
    }

    /**
     * @return int
     */
    public function setType()
    {
        if ($this->extension != null) {
            if (in_array($this->extension, self::IMAGE_EXTENSIONS)) {
                $this->type = self::TYPE_IMAGE;
            } else {
                $this->type = self::TYPE_OTHER_FILE;
            }
        } else {
            $this->type = self::TYPE_OTHER_FILE;
        }
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function upload()
    {
        $path = Yii::$app->ckfilemanager->uploadPath;
        $fileName = Yii::$app->ckfilemanager->useOriginalFilename ? $this->orig_name : $this->file_name;

        if (!file_exists($path)) {
            FileHelper::createDirectory($path);
        }

        if (is_writable($path) && $this->uploaded_file->saveAs($path . DIRECTORY_SEPARATOR . $fileName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function uploadThumbnail()
    {
        $path = Yii::$app->ckfilemanager->uploadPath . DIRECTORY_SEPARATOR . self::THUMBNAIL_DIRECTORY;
        $fileName = Yii::$app->ckfilemanager->useOriginalFilename ? self::THUMBNAIL . $this->orig_name : self::THUMBNAIL . $this->file_name;

        if (!file_exists($path)) {
            FileHelper::createDirectory($path);
        }

        if (is_writable($path) && $this->thumbnail->save($path . DIRECTORY_SEPARATOR . $fileName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function deleteFile()
    {
        $path = Yii::$app->ckfilemanager->uploadPath;
        $fileName = Yii::$app->ckfilemanager->useOriginalFilename ? $this->orig_name : $this->file_name;

        if($this->type == self::TYPE_IMAGE){
            $thumbPath = Yii::$app->ckfilemanager->uploadPath . DIRECTORY_SEPARATOR . self::THUMBNAIL_DIRECTORY;
            $thumbFileName = Yii::$app->ckfilemanager->useOriginalFilename ? self::THUMBNAIL . $this->orig_name : self::THUMBNAIL . $this->file_name;
        }

        if (file_exists($path . DIRECTORY_SEPARATOR . $fileName)) {
            FileHelper::unlink($path . DIRECTORY_SEPARATOR . $fileName);

            if($this->type == self::TYPE_IMAGE && file_exists($thumbPath . DIRECTORY_SEPARATOR . $thumbFileName)){
                FileHelper::unlink($thumbPath . DIRECTORY_SEPARATOR . $thumbFileName);
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isExistsFile()
    {
        $ckFile = self::findOne($this->id);
        $path = Yii::$app->ckfilemanager->uploadPath;

        if ($ckFile && (is_file($path . DIRECTORY_SEPARATOR . $ckFile->orig_name) || is_file($path . DIRECTORY_SEPARATOR . $ckFile->file_name))) {
            return true;
        }

        return false;
    }

    /**
     * @param $bytes
     * @return string
     */
    public static function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}
