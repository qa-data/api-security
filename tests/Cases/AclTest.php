<?php declare(strict_types = 1);

namespace Tests\Cases;

use QaData\ApiSecurity\Acl;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class AclTest extends TestCase
{

	public function testAllow(): void
	{
		$acl = new Acl();
		$acl->addRole('guest');
		$acl->addResource('test');

		Assert::false($acl->isAllowed('guest', 'test'));

		$acl->allow('guest', 'test');

		Assert::true($acl->isAllowed('guest', 'test'));
	}

	public function testDeny(): void
	{
		$acl = new Acl();
		$acl->addRole('guest');
		$acl->addResource('test');
		$acl->allow('guest', 'test');

		Assert::true($acl->isAllowed('guest', 'test'));

		$acl->deny('guest', 'test');

		Assert::false($acl->isAllowed('guest', 'test'));
	}

	public function testInheritanceAllow(): void
	{
		$acl = new Acl();
		$acl->addRole('guest');
		$acl->addRole('authenticated', 'guest');
		$acl->addResource('test');

		Assert::false($acl->isAllowed('guest', 'test'));
		Assert::false($acl->isAllowed('authenticated', 'test'));

		$acl->allow('authenticated', 'test');

		Assert::false($acl->isAllowed('guest', 'test'));
		Assert::true($acl->isAllowed('authenticated', 'test'));
	}

	public function testInheritanceDeny(): void
	{
		$acl = new Acl();
		$acl->addRole('guest');
		$acl->addRole('authenticated', 'guest');
		$acl->addResource('test');

		$acl->allow('guest', 'test');

		Assert::true($acl->isAllowed('guest', 'test'));
		Assert::true($acl->isAllowed('authenticated', 'test'));

		$acl->deny('guest', 'test');

		Assert::false($acl->isAllowed('guest', 'test'));
		Assert::false($acl->isAllowed('authenticated', 'test'));
	}

	public function testPrivilegeAllow(): void
	{
		$acl = new Acl();
		$acl->addRole('guest');
		$acl->addResource('test');

		Assert::false($acl->isAllowed('guest', 'test'));

		$acl->allow('guest', 'test', 'read');

		Assert::false($acl->isAllowed('guest', 'test'));
		Assert::true($acl->isAllowed('guest', 'test', 'read'));
		Assert::false($acl->isAllowed('guest', 'test', 'write'));
	}

	public function testPrivilegeDeny(): void
	{
		$acl = new Acl();
		$acl->addRole('guest');
		$acl->addResource('test');
		$acl->allow('guest', 'test');
		$acl->deny('guest', 'test', 'write');

		Assert::true($acl->isAllowed('guest', 'test'));
		Assert::false($acl->isAllowed('guest', 'test', 'write'));
	}

}

$test = new AclTest();
$test->run();
