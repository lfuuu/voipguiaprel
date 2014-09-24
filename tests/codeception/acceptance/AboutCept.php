<?php

use tests\codeception\_pages\AboutPage;

$I = new _AcceptanceTester($scenario);
$I->wantTo('ensure that about works');
AboutPage::openBy($I);
$I->see('About', 'h1');
