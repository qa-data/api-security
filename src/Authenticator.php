<?php

declare(strict_types = 1);

namespace QaData\ApiSecurity;

interface Authenticator
{

	function authenticate(string $token): Identity;

}
