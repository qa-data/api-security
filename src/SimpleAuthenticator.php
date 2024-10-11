<?php

declare(strict_types = 1);

namespace QaData\ApiSecurity;

class SimpleAuthenticator implements Authenticator
{

	/**
	 * @param array $tokens list of pairs username => password
	 * @param array $roles list of pairs username => role[]
	 * @param array $data list of pairs username => mixed[]
	 */
	public function __construct(
		#[\SensitiveParameter]
		private array $tokens,
		private array $roles = [],
		private array $data = [],
	)
	{
	}

	public function authenticate(
		#[\SensitiveParameter]
		string $token,
	): Identity
	{
		foreach ($this->tokens as $_id => $_token) {
			if (strcasecmp($_token, $token) === 0) {
				return new SimpleIdentity($_id, $_token, $this->roles[$_id] ?? [], $this->data[$_id] ?? []);
			}
		}

		throw new AuthenticationException("Invalid token.");
	}

}
