ifndef VERBOSE
.SILENT:
endif

include .docker/.env

COLOR_TITLE = \033[33m
COLOR_RESET = \033[0m

define echo-title
  printf "\n$(COLOR_TITLE)****************************************************************\n"
  printf "*$(1)\n"
  printf "$(COLOR_TITLE)****************************************************************$(COLOR_RESET)\n\n"
endef

define 	docker_php_exec
	$(call docker_container_exec, $(PHP_CONTAINER_NAME), deveolas, $(1))
endef

define symfony_exec
	$(call docker_php_exec, bin/console $(1))
endef

##
## Container
## ----
##
php: ## Start the php container
	$(call docker_php_exec, $(filter-out $@,$(MAKECMDGOALS)))

##
## Project
## -------
##

project-install: docker-compose-build dev-start composer-install ## Install the project and start the dev environment

project-remove: docker-compose-kill docker-compose-clean ## Stop the dev environment and remove generated files
	$(call echo-title, Removing files)
	rm -rf vendor var/cache/* var/log/* node_modules/

project-reinstall: project-remove project-install ## Destroy the dev environment, run a fresh install of the project, then start the dev environment

dev-start: ## Start the dev environment
	$(call echo-title, Starting docker)
	$(MAKE) docker-compose-up

dev-stop: ## Stop the dev environment
	$(call echo-title, Stopping docker)
	$(MAKE) docker-compose-down

composer-install: composer.json ## Install Symfony vendors as versioned in the project
	$(call echo-title, Installing Symfony vendors)
	$(call docker_php_exec, composer install --ignore-platform-reqs)
	@touch vendor

db-sf-load: wait-for-db ## Reset Symfony database with default data
	$(call echo-title, Loading Symfony database)
	$(call symfony_exec, doctrine:database:drop --force --if-exists)
	$(call symfony_exec, doctrine:database:create)
	$(call symfony_exec, doctrine:schema:create)

apache-log: ## Showing apache logs
	$(call echo-title, Showing apache logs)
	$(call docker_container_exec, $(APACHE_CONTAINER_NAME), root, tail -f /var/log/apache2/error.log)

acl:
	$(call echo-title, Setting file rights)
	setfacl -R -m u:"www-data":rwX -m u:$(whoami):rwx var finder translations

##
## Quality
## -------
##

.PHONY: tests

tests: ## Run unit tests
	$(call echo-title, Running tests)
	$(call symfony_exec, doctrine:schema:drop --env=test --force)
	$(call symfony_exec, doctrine:database:create --env=test)
	$(call symfony_exec, doctrine:schema:create --env=test)
	$(call docker_php_exec, composer phpunit)

symfony-security: ## Check security of your dependencies (https://security.sensiolabs.org/)
	$(call echo-title, Checking Symfony vendor security)
	$(call symfony_exec, security:check)

##
## Assets
##-------

npm: ## Update npm packages
	$(call echo-title, Installing npm)
	$(call docker_php_exec, npm install)

assets-watch: ## Watch the assets and build their development version on file change
	$(call echo-title, Watching assets changes)
	$(call docker_php_exec, npm run watch)

assets-dev: ## Build the development version of the assets
	$(call echo-title, Building dev assets)
	$(call docker_php_exec, npm run dev)

assets-server: ## Build the development version of the assets with hot reloading
	$(call echo-title, Building dev assets with hot reloading)
	$(call docker_php_exec, npm run server)

assets-prod: ## Build the production version of the assets
	$(call echo-title, Building prod assets)
	$(call docker_php_exec, npm run prod)


##
## Debug
##-------
xdebug-on: ## Enable xdebug
	export XDEBUG_MODE=develop,debug && $(DOCKER_COMPOSE) up -d --force-recreate php

xdebug-off: ## Disable xdebug
	export XDEBUG_MODE=off && $(DOCKER_COMPOSE) up -d --force-recreate php

include .docker/Makefile


##
#
# Internal rules
# --------------
#
wait-for-db:
	$(call echo-title, Waiting for db)
	$(call docker_wait, tcp,$(DATABASE_CONTAINER_NAME),$(DATABASE_PORT))



.DEFAULT_GOAL := help
help:
	@grep -Eh '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
