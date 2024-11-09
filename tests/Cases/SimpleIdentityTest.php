<?php declare(strict_types = 1);

namespace Tests\Cases;

use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class SimpleIdentityTest extends TestCase
{

	public function testWithBody(): void
	{
		Assert::same(1, 1);
	}

	protected function setUp(): void
	{
		parent::setUp();
	}

}

$test = new SimpleIdentityTest();
$test->run();
