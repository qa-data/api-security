.PHONY: install qa lint lintfix phpstan tests

install:
	composer update

qa: phpstan lint tests

lint:
	vendor/bin/phpcs --standard=ruleset.xml --encoding=utf-8 -np src tests

lintfix:
	vendor/bin/phpcbf --standard=ruleset.xml --encoding=utf-8 -np src tests

phpstan:
	vendor/bin/phpstan analyse -c phpstan.neon

tests:
	vendor/bin/tester -s -p php --colors 1 -C tests/Cases
