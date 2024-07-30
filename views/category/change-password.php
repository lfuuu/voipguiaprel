<?php
$this->title = 'Смена пароля';
$this->params['breadcrumbs'][] = $this->title;

use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="container mt-3">
    <div class="card border-light rounded-lg">
        <div class="card-body">
            <h4 class="card-title mb-3"><?= Html::encode($this->title) ?></h4>

            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'currentPassword')->passwordInput(['class' => 'form-control'])->label('Старый пароль') ?>
            <?= $form->field($model, 'newPassword')->passwordInput(['class' => 'form-control'])->label('Новый пароль') ?>
            <?= $form->field($model, 'newPasswordRepeat')->passwordInput(['class' => 'form-control'])->label('Повторение пароля') ?>

            <div class="form-group mt-4">
                <?= Html::submitButton('Изменить пароль', ['class' => 'btn btn-primary btn-block']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<style>
    body {
        background-color: #f8f9fa;
    }

    .container {
        margin-top: 10px;
        margin-left: 10px;
        max-width: 500px; /* Set a maximum width for the form container */
    }

    .card {
        border: 1px solid #d3d3d3; /* Light gray border to match the screenshot */
        border-radius: 5px; /* Small rounding for the card */
    }

    .form-control {
        border-radius: 5px; /* Small rounding for the form controls */
    }

    .btn {
        border-radius: 5px; /* Small rounding for the button */
    }

    .card-body {
        padding: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .text-muted {
        margin-bottom: 1.5rem;
    }
</style>
