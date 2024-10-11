<?php

declare(strict_types = 1);

namespace QaData\ApiSecurity;

/**
 * @property array<string> $roles
 * @property array<mixed>  $data
 */
class SimpleIdentity implements Identity
{

	public function __construct(
		private readonly string|int $id,
		private readonly string $token,
		private readonly array $roles = [],
		private readonly array $data = [],
	)
	{
	}

	function getId(): string|int
	{
		return $this->id;
	}

	function getToken(): string
	{
		return $this->token;
	}

	function getRoles(): array
	{
		return $this->roles;
	}

	function getData(): array
	{
		return $this->data;
	}

}
