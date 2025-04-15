.PHONY: container install phpcbf phpcs test test_make

docker_run=docker run --rm -t -v .:/code -e DOCROOT=/code/vendor/mink/driver-testsuite/web-fixtures registry.gitlab.com/behat-chrome/docker-chrome-headless

# This is used to validate the makefile by calling each command with `-n`.
test_make: container install phpcs phpcbf test

container:
	$(docker_run) bash

install:
	$(docker_run) composer install --no-interaction

phpcbf: install
	$(docker_run) vendor/bin/phpcbf

phpcs: install
	$(docker_run) vendor/bin/phpcs

test: install
	$(docker_run) bash -c "sleep 3 && vendor/bin/phpunit"
