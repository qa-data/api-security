<?php declare(strict_types = 1);

namespace QaData\ApiSecurity\DI;

use Nette;
use Nette\Schema\Expect;
use QaData\ApiSecurity\Acl;
use QaData\ApiSecurity\Authorizator;
use QaData\ApiSecurity\User;

final class ApiSecurityExtension extends Nette\DI\CompilerExtension
{

	public function getConfigSchema(): Nette\Schema\Schema
	{
		return Nette\Schema\Expect::structure([
			'roles' => Expect::arrayOf('string|array|null'),
			'resources' => Expect::arrayOf('string|null'),
		]);
	}

	public function loadConfiguration(): void
	{
		/** @var object{roles: array, resources: array} $config */
		$config = $this->getConfig();

		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('user'))
			->setFactory(User::class);

		if ($config->roles || $config->resources) {
			$authorizator = $builder->addDefinition($this->prefix('authorizator'))
				->setType(Authorizator::class)
				->setFactory(Acl::class);

			foreach ($config->roles as $role => $parents) {
				$authorizator->addSetup('addRole', [$role, $parents]);
			}

			foreach ($config->resources as $resource => $parents) {
				$authorizator->addSetup('addResource', [$resource, $parents]);
			}

			if ($this->name === 'apiSecurity') {
				$builder->addAlias('nette.authorizator', $this->prefix('authorizator'));
			}
		}

		if ($this->name === 'apiSecurity') {
			$builder->addAlias('user', $this->prefix('user'));
		}

	}

}
