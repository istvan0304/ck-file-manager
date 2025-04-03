<?php

namespace istvan0304\ckfilemanager\components;

use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class CkFileManagerComponent
 * @package istvan0304\ckfilemanager\components
 */
class CkFileManagerComponent extends Component
{
    /**
     * Uploaded files path.
     * @var string
     */
    public $uploadPath = 'uploads/files';

    /**
     * @var boolean $useOriginalFilename use original filename
     */
    public $useOriginalFilename = true;

    /**
     * @var bool $allowDuplicateFile Let you to upload a file more than one times
     */
    public $allowDuplicateFile = false;

    /**
     * @var bool $maxImageFileSizeUpload Maximum images file size.
     */
    public $maxImageFileSizeUpload = 3 * 1024 * 1024;       // 3MB

    /**
     * @var bool $maxFileSizeUpload Maximum not images file size.
     */
    public $maxFileSizeUpload = 10 * 1024 * 1024;       // 10MB

    /**
     * @var null $imageManagerRbacRule Rbac rule name for image manager list
     */
    public $imageManagerRbacRule = null;

    /**
     * Init set config
     */
    public function init() {
        parent::init();

        // Check if the user input is correct
        $this->checkAttributes();
    }

    /**
     * Check the user configurable variables.
     * @throws InvalidConfigException
     */
    private function checkAttributes()
    {
        if (! is_string($this->uploadPath)) {
            throw new InvalidConfigException("File upload file path '$this->uploadPath' is not a string");
        }
    }
}
