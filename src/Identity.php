<?php

declare(strict_types = 1);

namespace QaData\ApiSecurity;

interface Identity
{

	function getId(): string|int;

	function getToken(): string;

	function getRoles(): array;

}
