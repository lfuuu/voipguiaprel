<?php
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use \kartik\select2\Select2;
?>
<div class="container">
    <?php if ($model->name): ?>
        <h4>Редактирование роли <?=$model->name?></h4>
    <?php else: ?>
        <h4>Создание роли</h4>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(); ?>
    
    <?php if ($model->name): ?>
        <?= $form->field($model, 'name')->textInput(['autocomplete'=>'off', 'disabled' => 'disabled']); ?>
    <?php else: ?>
        <?= $form->field($model, 'name')->textInput(['autocomplete'=>'off']); ?>
    <?php endif; ?>
    <?= $form->field($model, 'description')->textInput(['autocomplete'=>'off']); ?>
    
    <div class="row">
        <div class="col-sm-12">
            <label>Права доступа</label>
            <?= Select2::widget([
                'name' => 'roleAcl[]',
                'value' => $roleAclPairs,
                'data' => $aclPairs,
                'options' => [
                    'multiple' => true,
                ],
            ])
            ?>
        </div>
    </div>

    <br />
    
    <div class="form-group">
        <label class="col-sm-2 control-label"></label>
        <div class="col-sm-10">
            <button type="submit" class="btn btn-success">Сохранить</button>
            <a href="<?=Url::toRoute(['role/list'])?>" class="btn btn-default btn-sm">Закрыть</a>
        </div>
    </div>

    <?php $form->end() ?>
</div>
