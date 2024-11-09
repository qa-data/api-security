<?php declare(strict_types = 1);

namespace Tests\Cases;

use QaData\ApiSecurity\AuthenticationException;
use QaData\ApiSecurity\SimpleAuthenticator;
use QaData\ApiSecurity\SimpleIdentity;
use Tester\Assert;
use Tester\TestCase;
use function assert;

require __DIR__ . '/../bootstrap.php';

final class SimpleAuthenticatorTest extends TestCase
{

	public function testAuthenticate(): void
	{
		$authenticator = new SimpleAuthenticator(
			tokens: [
				'token1' => 'token1',
				'token2' => 'token2',
				'token3' => 'token3',
			],
			roles: [
				'token1' => ['admin'],
				'token2' => ['user'],
				'token3' => ['user'],
			],
			data: [
				'token1' => ['name' => 'John'],
				'token2' => ['name' => 'Jane'],
				'token3' => ['name' => 'Joe'],
			],
		);

		$identity = $authenticator->authenticate('token1');
		assert($identity instanceof SimpleIdentity);

		Assert::same('token1', $identity->getId());
		Assert::same('token1', $identity->getToken());
		Assert::same(['admin'], $identity->getRoles());
		Assert::same('John', $identity->getData()['name']);

		$identity = $authenticator->authenticate('token2');
		assert($identity instanceof SimpleIdentity);

		Assert::same('token2', $identity->getId());
		Assert::same('token2', $identity->getToken());
		Assert::same(['user'], $identity->getRoles());
		Assert::same('Jane', $identity->getData()['name']);

		Assert::exception(
			static fn () => $authenticator->authenticate('token4'),
			AuthenticationException::class,
			'Invalid token.',
		);
	}

}

$test = new SimpleAuthenticatorTest();
$test->run();
