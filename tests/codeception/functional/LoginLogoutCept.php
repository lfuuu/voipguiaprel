<?php

$I = new _FunctionalTester($scenario);
$I->wantTo('зайти на главную');

$I->amOnPage('/');
$I->see('Введите логин и пароль');

$I->fillField('LoginForm[username]', 'unknown_user');
$I->fillField('LoginForm[password]', 'unknown_password');
$I->click('Войти');

$I->see('Incorrect username or password');

$I->fillField('LoginForm[username]', 'test');
$I->fillField('LoginForm[password]', 'test');
$I->click('Войти');

$I->see('Выход');
$I->dontSee('Введите логин и пароль');

$I->click('Выход');

$I->see('Введите логин и пароль');