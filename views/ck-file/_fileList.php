<?php

use istvan0304\ckfilemanager\models\CkFile;
use yii\helpers\{Url, StringHelper};

/* @var $this \yii\web\View */
/* @var $ckFiles */

?>

<?php if (!empty($ckFiles)): ?>
    <?php foreach ($ckFiles as $ckFile): ?>
        <?php if ($ckFile->isExistsFile()): ?>
            <div class="ck-file-container">
                <div class="ck-file-box" data-id="<?= $ckFile->id ?>">
                    <?php if($ckFile->type == CkFile::TYPE_IMAGE): ?>
                    <img src="<?= Url::to(['ck-file/preview-thumbnail', 'id' => $ckFile->id]) ?>" class="ck-img"
                         alt="">
                    <?php else: ?>
                        <i class="fas fa-file-alt other-file-preview"></i>
                    <?php endif; ?>
                    <p class="ck-file-name"
                       title="<?= $ckFile->orig_name ?>"><?= StringHelper::truncate($ckFile->orig_name, 15) ?><br>
                        <span class="ck-file-data"><?= $ckFile->create_time ?></span><br>
                        <span class="ck-file-data"><?= CkFile::formatSizeUnits($ckFile->size) ?></span>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php else: ?>
    <div id="no-file">
        <div class="no-file-content">

            <i class="fas fa-folder-open"></i>
            <h3><?= Yii::t('ckfile', 'Nothing found.') ?></h3>
        </div>
    </div>
<?php endif; ?>
