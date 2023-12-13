<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers ::checkPassword
 */
class CheckPasswordTest extends TestCase
{
	/**
	 * @dataProvider wrongDataProviderForCheckPassword
	 *
	 * @param mixed $password
	 * @param mixed $hash
	 * @param mixed $isLegacy
	 * @param mixed $email
	 */
	public function testCheckPasswordWrongData($password, $hash, $isLegacy, $email): void
	{
		$this->assertFalse(checkPassword($password, $hash, $isLegacy, $email));
	}

	/**
	 * @dataProvider rightDataProviderForCheckPassword
	 *
	 * @param mixed $password
	 * @param mixed $hash
	 * @param mixed $isLegacy
	 * @param mixed $email
	 */
	public function testCheckPasswordRightData($password, $hash, $isLegacy, $email): void
	{
		$this->assertTrue(checkPassword($password, $hash, $isLegacy, $email));
	}

	public function wrongDataProviderForCheckPassword(): array
	{
		return [
			[null, null, null, null], // all null
			['test', null, null, null], // one value, all null
			[null, 'test', null, null], //  one value, all null
			[null, null, false, null], // one value, all null
			[null, null, null, 'email'], //  one value, all null
			[null, null, true, 'email'], //  one value, all null
			['test', 'test', true, 'test'], // legacy, wrong hash
			['test', 'test', false, 'test'], // wrong hash (not legacy)
			['test', 'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3', false, ''], //sha1 not legacy
			['test1', 'f44403bf248cecf543b8daa2f763919e453da053', true, 'email'], //sha1 legacy, wrong password
		];
	}

	public function rightDataProviderForCheckPassword(): array
	{
		return [
			['test', 'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3', true, ''], //legacy without email
			['test', 'f44403bf248cecf543b8daa2f763919e453da053', true, 'email'], // legacy with email
			['test', '$2y$10$7uZKLSRD3bjHUsZqkYDhaOJk9xookssYEi7lURh4XdKO7C.zNxzvW', false, ''], //hash with PASSWORD_DEFAULT
			['test', '$2y$10$c8slv1qRDozN1wl3rrMFCOZzXftcmE6hGXCKeD81RG/pX6YYNIwBm', false, ''], //hash with PASSWORD_BCRYPT
			['rasmuslerdorf', '$argon2i$v=19$m=1024,t=2,p=2$YzJBSzV4TUhkMzc3d3laeg$zqU/1IN0/AogfP4cmSJI1vc8lpXRW9/S0sYY2i2jHT0', false, ''], //hash with Argon2i
		];
	}
}
