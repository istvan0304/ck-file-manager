<?php

namespace istvan0304\filemanager\components;

use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class CkFileManagerComponent
 * @package istvan0304\filemanager\components
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
