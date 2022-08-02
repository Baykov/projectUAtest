<?php

use app\models\User;

class AuthTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
	public function testAuth()
	{
		$user = User::findByUsername('admin');

		$this->assertFalse($user->validatePassword(''));

		$this->assertTrue($user->validatePassword('admin'));

		$user->password = 'admin';
		$this->assertTrue(Yii::$app->user->login($user, 0));
	}
}