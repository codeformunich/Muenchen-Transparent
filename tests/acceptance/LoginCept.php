<?php 


$I = new AcceptanceTester($scenario);
$I->wantTo('log in');
$I->amOnPageValidated('/benachrichtigungen');
$I->fillField('email', 'user@example.com');
$I->fillField('password', '1234');
// TODO: write a helper for the next line when there are more tests working through XSS protection
$I->submitForm('#login', [$I->grabAttributeFrom('#login', 'name') => ""]); 
$I->see('Benachrichtigungen an user@example.com:');
