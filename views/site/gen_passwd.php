<?php

/*
nice script for creating a hash with random salt
*/

function rand_str($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
{
    $str = '';
    $count = strlen($charset);
    while ($length--) {
        $str .= $charset[mt_rand(0, $count-1)];
    }
    return $str;
}

$hash = "";

if(isset($_POST['string']) && !empty($_POST['string']) && is_string($_POST['string']))
{
    $hash = Yii::$app->security->generatePasswordHash($_POST['string']);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title></title>
</head>
<body>
<h1>Hash</h1>

<?= \yii\helpers\Html::beginForm() ?>
    <table>
        <tr>
            <td>
                <input type ="password"
                       value ="<?php if($hash!== ""){ echo htmlspecialchars($_POST['string']); } ?>"
                       id ="string"
                       name ="string" />
            </td>
            <td>
                <input type ="submit" value ="hash" />
            </td>
        </tr>
    </table>
    <?= \yii\helpers\Html::endForm() ?>
<h4><?= $hash?></h4>
</body>
</html>