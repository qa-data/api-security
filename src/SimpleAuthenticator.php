<?php declare(strict_types = 1);

namespace QaData\ApiSecurity;

use function strcasecmp;

class SimpleAuthenticator implements Authenticator
{

	/**
	 * @param array<string, string> $tokens list of pairs username => password
	 * @param array<string, array<string>> $roles list of pairs => role[]
	 * @param array<string, mixed> $data list of pairs username => mixed[]
	 */
	public function __construct(
		private readonly array $tokens,
		private readonly array $roles = [],
		private readonly array $data = [],
	)
	{
	}

	public function authenticate(string $token): Identity
	{
		foreach ($this->tokens as $_id => $_token) {
			if (strcasecmp($_token, $token) === 0) {
				return new SimpleIdentity(
					id: $_id,
					token: $_token,
					roles: $this->roles[$_id] ?? [],
					data: $this->data[$_id] ?? [],
				);
			}
		}

		throw new AuthenticationException('Invalid token.');
	}

}
