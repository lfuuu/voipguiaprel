<?php
use tests\codeception\_pages\IndexPage;

$I = new _FunctionalTester($scenario);
$I->wantTo('ensure that about works');

$I->amOnPage(['site/index']);
$I->see('Введите логин и пароль');

$I->fillField('LoginForm[username]', 'test');
$I->fillField('LoginForm[password]', 'test');
$I->click('Войти');


$I->see('Введите логин и пароль');