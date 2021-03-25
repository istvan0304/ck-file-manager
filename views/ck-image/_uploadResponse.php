<?php

use Yii;

/* @var $this \yii\web\View */
/* @var $successUpload */
/* @var $filesNumber */
/* @var $responsesData */

?>

<p><?= Yii::t('ckimage', 'Uploaded: ') ?><?= $successUpload ?>/<?= $filesNumber ?>...</p>
<?php foreach ($responsesData as $key => $responseData): ?>
    <div class="ck-response-box <?= $responseData['class'] ?>">
        <p><strong><?= $key ?></strong></p>
        <p>
            <?php
            if (is_string($responseData['message'])):
                echo $responseData['message'];
            endif;
            ?>
        </p>
    </div>
<?php endforeach; ?>
