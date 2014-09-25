<?php
use yii\helpers\Html;
?>
<?= Html::beginForm('' , 'post', ['id' => 'loginForm']) ?>
<div style="text-align: center">
    <br/>
    <br/>
    <div style="width: 350px; display: inline-block">
        <table cellSpacing=4 cellPadding=2 width="100%" border=0 style="border: 1px solid #E0E0E0; background-color: #F7F7F7;">
            <tr>
                <td colspan="2">
                    <?php
                    foreach ($model->getErrors() as $errorGroup) {
                        foreach ($errorGroup as $error) {
                            echo "<b style='color:red'>" . $error . '</b><br/>';
                        }
                    }
                    ?>
                    <h3>Введите логин и пароль:</h3>
                </td>
            </tr>
            <tr>
                <td width="30%">Логин:</td>
                <td width="70%">
                    <?= Html::activeTextInput($model, 'username', ['id'=>'username']) ?>
                </td>
            </tr>
            <tr>
                <td width="30%">Пароль:</td>
                <td width="70%">
                    <?= Html::activePasswordInput($model, 'password') ?>
                </td>
            </tr>
            <tr><td>&nbsp;</td></tr>
            <tr>
                <td></td>
                <td>
                    <input id="login" type=submit value='Войти' style="margin-right: 35px;">
                </td>
            </tr>
        </table>
    </div>
</div>
<?= Html::endForm() ?>