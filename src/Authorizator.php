<?php declare(strict_types = 1);

namespace QaData\ApiSecurity;

interface Authorizator
{

	public const All = null;

	public const Allow = true;

	public const Deny = false;

	public function isAllowed(string|null $role, string|null $resource, string|null $privilege): bool;

}
