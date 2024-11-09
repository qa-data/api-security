<?php declare(strict_types = 1);

namespace QaData\ApiSecurity;

use Nette\Utils\Arrays;
use RuntimeException;
use function func_get_args;
use function in_array;

class User
{

	public string $guestRole = 'guest';

	public string $authenticatedRole = 'authenticated';

	/** @var array<callable> function (User $sender): void; Occurs after successful login */
	public array $onLoggedIn = [];

	private Identity|null $identity = null;

	private bool $authenticated = false;

	public function __construct(
		private readonly Authenticator|null $authenticator = null,
		private readonly Authorizator|null $authorizator = null,
	)
	{
	}

	public function isLoggedIn(): bool
	{
		return $this->authenticated;
	}

	public function getIdentity(): Identity|null
	{
		return $this->identity;
	}

	public function getAuthenticator(): Authenticator
	{
		if (!$this->authenticator) {
			throw new RuntimeException('Authenticator has not been set.');
		}

		return $this->authenticator;
	}

	public function isInRole(string $role): bool
	{
		return in_array($role, $this->getRoles(), true);
	}

	public function login(string|Identity $token): void
	{
		if ($token instanceof Identity) {
			$this->identity = $token;
		} else {
			$authenticator = $this->getAuthenticator();
			$this->identity = $authenticator->authenticate(...func_get_args());
		}

		$this->authenticated = true;
		Arrays::invoke($this->onLoggedIn, $this);
	}

	public function getId(): string|int|null
	{
		return $this->getIdentity()?->getId();
	}

	/**
	 * @return array<string>
	 */
	public function getRoles(): array
	{
		if (!$this->isLoggedIn()) {
			return [$this->guestRole];
		}

		$identity = $this->getIdentity();

		return $identity && $identity->getRoles() ? $identity->getRoles() : [$this->authenticatedRole];
	}

	public function isAllowed(
		string|null $resource = Authorizator::All,
		string|null $privilege = Authorizator::All,
	): bool
	{
		foreach ($this->getRoles() as $role) {
			if ($this->authorizator?->isAllowed($role, $resource, $privilege)) {
				return true;
			}
		}

		return false;
	}

}
