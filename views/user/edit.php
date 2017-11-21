<?php
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>
<div class="container">
    <?php if ($model->id): ?>
        <h4>Редактирование пользователя <?=$model->name?></h4>
    <?php else: ?>
        <h4>Создание пользователя</h4>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'login')->textInput(['autocomplete'=>'off']); ?>
    <?= $form->field($model, 'name')->textInput(['autocomplete'=>'off']); ?>
    <?= $form->field($model, 'password')->passwordInput(['autocomplete'=>'off']); ?>
    <?= $form->field($model, 'role')->dropDownList($rolePairs); ?>

    <div class="form-group">
        <label class="col-sm-2 control-label"></label>
        <div class="col-sm-10">
            <button type="submit" class="btn btn-success">Сохранить</button>
            <a href="<?=Url::toRoute(['user/list'])?>" class="btn btn-default btn-sm">Закрыть</a>
        </div>
    </div>

    <?php $form->end() ?>
</div>
