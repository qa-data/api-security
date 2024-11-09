<?php declare(strict_types = 1);

namespace QaData\ApiSecurity;

interface Authenticator
{

	public function authenticate(string $token): Identity;

}
