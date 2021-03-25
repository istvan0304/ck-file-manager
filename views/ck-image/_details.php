<?php

use Yii;
use istvan0304\ckfilemanager\models\CkImage;

/* @var $this \yii\web\View */
/* @var $ckImageArray */

?>

<div class="ck-selected-img-container">
    <div class="ck-selected-img">
        <img src="<?= '/ckfilemanager/ck-image/preview-thumbnail?id=' . $ckImageArray['id'] ?>" class="ck-img">
    </div>
    <p class="ck-detail ck-selected-img-name"><?= $ckImageArray['orig_name'] ?></p>
    <small class="ck-detail ck-selected-img-size"><?= CkImage::formatSizeUnits($ckImageArray['size']) ?></small>
</div>

<button class="ck-btn ck-btn-first" id="ck-select" data-id="<?= $ckImageArray['id'] ?>"><?= Yii::t('ckimage', 'Select') ?></button>
<button class="ck-btn ck-btn-third" id="ck-delete" data-id="<?= $ckImageArray['id'] ?>"><i class="fas fa-trash-alt"></i> <?= Yii::t('ckimage', 'Delete') ?></button>
