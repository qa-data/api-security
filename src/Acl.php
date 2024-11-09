<?php declare(strict_types = 1);

namespace QaData\ApiSecurity;

class Acl implements Authorizator
{

	/** @var array<string, array<mixed>> */
	private array $roles = [];

	/** @var array<string, array<mixed>> */
	private array $resources = [];

	/** @var array<string, array<mixed>> */
	private array $permissions = [];

	/** @var array<string> */
	private array $roleParents = [];

	public function addRole(string $role, string|null $parentRole = null): void
	{
		$this->roles[$role] = [];

		if ($parentRole === null) {
			return;
		}

		$this->roleParents[$role] = $parentRole;
	}

	public function addResource(string $resource): void
	{
		$this->resources[$resource] = [];
	}

	public function allow(string $role, string $resource, string|null $privilege = null): void
	{
		if ($privilege !== null) {
			$this->permissions[$role][$resource][$privilege] = true;
		} else {
			$this->permissions[$role][$resource]['_all'] = true;
		}
	}

	public function deny(string $role, string $resource, string|null $privilege = null): void
	{
		if ($privilege !== null) {
			$this->permissions[$role][$resource][$privilege] = false;
		} else {
			$this->permissions[$role][$resource]['_all'] = false;
		}
	}

	public function isAllowed(string|null $role, string|null $resource, string|null $privilege = null): bool
	{
		if (
			$role === null
			|| $resource === null
			|| !isset($this->roles[$role])
			|| !isset($this->resources[$resource])
		) {
			return false;
		}

		if ($privilege !== null) {
			if (
				isset($this->permissions[$role][$resource][$privilege])
				&& $this->permissions[$role][$resource][$privilege] === false
			) {
				return false;
			}

			if (
				isset($this->permissions[$role][$resource][$privilege])
				&& $this->permissions[$role][$resource][$privilege] === true
			) {
				return true;
			}
		}

		if (
			isset($this->permissions[$role][$resource]['_all'])
			&& $this->permissions[$role][$resource]['_all'] === false
		) {
			return false;
		}

		if (
			isset($this->permissions[$role][$resource]['_all'])
			&& $this->permissions[$role][$resource]['_all'] === true
		) {
			return true;
		}

		if (isset($this->roleParents[$role])) {
			return $this->isAllowed($this->roleParents[$role], $resource, $privilege);
		}

		return false;
	}

}
