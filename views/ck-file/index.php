<?php

/* @var $this \yii\web\View */
/* @var $ckImageManagerForm istvan0304\ckfilemanager\models\CkImageForm */

/* @var $ckImages */

use yii\widgets\Pjax; ?>

<div class="ck-details">
    <div class="ck-details-header">
        <h4><?= Yii::t('ckfile', 'Details') ?></h4>
        <a href="#" class="ck-close ck-details-close"></a>
    </div>
    <div class="ck-details-body"></div>
</div>

<?php echo $this->render('_handlerRow',
    [
        'ckFileManagerForm' => $ckFileManagerForm,
        'acceptFiles' => $acceptFiles
    ]); ?>

<div id="ck-upload-status">
    <a href="#" class="ck-close ck-progress-close"></a>
    <div class="ck-progress-container">
        <div class="ck-progress-line">
            <div class="ck-progress"><span id="ck-percentage"></span></div>
        </div>
        <button id="ck-upload-details" class="ck-btn ck-btn-second"><i
                    class="fas fa-info-circle"></i> <?= Yii::t('ckfile', 'Details') ?></button>
    </div>

</div>

<?php Pjax::begin(['id' => 'ck-pjax-file-list', 'options' => ['class' => 'ck-file-list']]); ?>
<?php echo $this->render('_fileList',
    [
        'ckFiles' => $ckFiles
    ]); ?>

<?php Pjax::end(); ?>

<div id="ck-list-loader" class="justify-content-center">
    <div class="ck-loader-blur"></div>
    <div class="ck-spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>

<aside id="ck-sidebar">
    <p class="ck-no-select"><?= Yii::t('ckfile', 'No image selected.') ?></p>
    <div class="ck-sidebar-content"></div>
</aside>
