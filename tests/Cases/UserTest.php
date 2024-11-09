<?php declare(strict_types = 1);

namespace Tests\Cases;

use QaData\ApiSecurity\Acl;
use QaData\ApiSecurity\AuthenticationException;
use QaData\ApiSecurity\SimpleAuthenticator;
use QaData\ApiSecurity\SimpleIdentity;
use QaData\ApiSecurity\User;
use Tester\Assert;
use Tester\TestCase;
use function assert;

require __DIR__ . '/../bootstrap.php';

final class UserTest extends TestCase
{

	public function testFailedLogin(): void
	{
		$user = $this->createUser();

		Assert::exception(
			static fn () => $user->login('dummyToken'),
			AuthenticationException::class,
			'Invalid token.',
		);
		Assert::null($user->getIdentity());
	}

	public function testSuccessLogin(): void
	{
		$user = $this->createUser();
		$user->login('adminToken');

		Assert::true($user->isLoggedIn());
		Assert::type('QaData\ApiSecurity\SimpleIdentity', $user->getIdentity());
	}

	public function testIsInRole(): void
	{
		$user = $this->createUser();
		$user->login('adminToken');

		Assert::true($user->isInRole('admin'));
		Assert::false($user->isInRole('authenticated'));
		Assert::false($user->isInRole('guest'));
		Assert::false($user->isInRole('dummyRole'));
	}

	public function testRoles(): void
	{
		$user = $this->createUser();
		$user->login('adminToken');

		Assert::same(['admin'], $user->getIdentity()?->getRoles() ?? []);
	}

	public function testUserData(): void
	{
		$user = $this->createUser();
		$user->login('adminToken');

		$identity = $user->getIdentity();
		assert($identity instanceof SimpleIdentity);

		Assert::same(['name' => 'Honza', 'surname' => 'Náhodný'], $identity->getData());
	}

	public function testIsAllowed(): void
	{
		$user = $this->createUser();
		$user->login('adminToken');

		Assert::true($user->isAllowed('allowedResource'));
		Assert::false($user->isAllowed('deniedResource'));
	}

	private function createUser(): User
	{
		$authenticator = new SimpleAuthenticator(
			[
				'admin' => 'adminToken',
			],
			[
				'admin' => ['admin'],
			],
			[
				'admin' => [
					'name' => 'Honza',
					'surname' => 'Náhodný',
				],
			],
		);

		$authorizator = new Acl();
		$authorizator->addRole('guest');
		$authorizator->addRole('authenticated', 'guest');
		$authorizator->addRole('admin', 'authenticated');
		$authorizator->addResource('allowedResource');
		$authorizator->addResource('deniedResource');
		$authorizator->allow('authenticated', 'allowedResource');

		return new User($authenticator, $authorizator);
	}

}

$test = new UserTest();
$test->run();
