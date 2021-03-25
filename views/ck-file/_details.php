<?php

use Yii;
use istvan0304\ckfilemanager\models\CkFile;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $ckFileArray */

?>

<div class="ck-selected-file-container">
    <div class="ck-selected-file">
        <?php if($ckFileArray['type'] == CkFile::TYPE_IMAGE): ?>
            <img src="<?= Url::to('/ckfilemanager/ck-file/preview-thumbnail?id=' . $ckFileArray['id']) ?>" class="ck-img">
        <?php else: ?>
            <i class="fas fa-file-alt other-file-preview"></i>
        <?php endif; ?>
    </div>
    <p class="ck-detail ck-selected-file-name"><?= $ckFileArray['orig_name'] ?></p>
    <small class="ck-detail ck-selected-file-size"><?= CkFile::formatSizeUnits($ckFileArray['size']) ?></small>
</div>

<button class="ck-btn ck-btn-first" id="ck-select" data-id="<?= $ckFileArray['id'] ?>"><?= Yii::t('ckfile', 'Select') ?></button>
<button class="ck-btn ck-btn-third" id="ck-delete" data-id="<?= $ckFileArray['id'] ?>"><i class="fas fa-trash-alt"></i> <?= Yii::t('ckfile', 'Delete') ?></button>
