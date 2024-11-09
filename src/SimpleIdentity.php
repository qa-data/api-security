<?php declare(strict_types = 1);

namespace QaData\ApiSecurity;

class SimpleIdentity implements Identity
{

	/**
	 * @param array<int, string> $roles
	 * @param array<string, mixed> $data
	 */
	public function __construct(
		private readonly string|int $id,
		private readonly string $token,
		private readonly array $roles = [],
		private readonly array $data = [],
	)
	{
	}

	public function getId(): string|int
	{
		return $this->id;
	}

	public function getToken(): string
	{
		return $this->token;
	}

	/**
	 * @return array<string>
	 */
	public function getRoles(): array
	{
		return $this->roles;
	}

	/**
	 * @return array<mixed>
	 */
	public function getData(): array
	{
		return $this->data;
	}

}
