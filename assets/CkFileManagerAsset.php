<?php

namespace istvan0304\ckfilemanager\assets;

use yii\web\AssetBundle;

/**
 * Class CkFileManagerAsset
 * @package istvan0304\ckfilemanager\assets
 */
class CkFileManagerAsset extends AssetBundle
{
    public $sourcePath = '@vendor/istvan0304/ckfilemanager/assets';

    public $css = [
        'css/all.min.css',
        'css/ckFileManager.css',
    ];

    public $js = [
        'js/ckFileManager.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
    ];
}
