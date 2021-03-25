<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $ckFileManagerForm, istvan0304\imagemanager\models\CkFileForm */
/* @var $acceptFiles */

?>

<div class="ck-handle-row">
    <button id="ck-file-upload" class="ck-btn ck-btn-second"><i class="fas fa-upload"></i> <?= Yii::t('ckfile', 'Upload') ?></button>

    <?php $form = ActiveForm::begin(['id' => 'file-upload-form', 'enableClientValidation' => false]); ?>

    <?= $form->field($ckFileManagerForm, 'uploaded_files[]')->fileInput(['multiple' => true, 'accept' => $acceptFiles])->label(false) ?>

    <?php ActiveForm::end(); ?>

    <?= Html::textInput('search', null, ['id' => 'ck-search', 'placeholder' => Yii::t('ckfile', 'Search...')]) ?>
</div>
