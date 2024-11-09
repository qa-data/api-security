<?php declare(strict_types = 1);

namespace QaData\ApiSecurity;

interface Identity
{

	public function getId(): string|int;

	public function getToken(): string;

	/**
	 * @return array<string>
	 */
	public function getRoles(): array;

}
