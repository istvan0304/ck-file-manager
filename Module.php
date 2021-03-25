<?php

namespace istvan0304\ckfilemanager;

use Yii;
use yii\base\Module as BaseModule;

/**
 * Class Module
 * @package istvan0304\ckfilemanager
 */
class Module extends BaseModule
{
    public $defaultRoute = 'ck-file';
    
    public function init()
    {
        parent::init();

        if (!isset(Yii::$app->i18n->translations['ckfile'])) {
            Yii::$app->i18n->translations['ckfile'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en',
                'basePath' => '@istvan0304/ckfilemanager/messages'
            ];
        }
    }
}