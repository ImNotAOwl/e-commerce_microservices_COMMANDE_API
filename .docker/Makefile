DOCKER_DIR = $(dir $(lastword $(MAKEFILE_LIST)))
DOCKER_COLOR_TITLE=$(shell tput -Txterm setaf 6)
DOCKER_COLOR_RESET=\033[0m

DOCKER_COMPOSE = cd $(DOCKER_DIR) && docker-compose

define docker-title
  printf "\n$(DOCKER_COLOR_TITLE)****************************************************************\n"
  printf "* [DOCKER]$(1)\n"
  printf "$(DOCKER_COLOR_TITLE)****************************************************************$(DOCKER_COLOR_RESET)\n\n"
endef

define docker-text
  printf "$(DOCKER_COLOR_TITLE)* [DOCKER]$(1)$(DOCKER_COLOR_RESET)\n\n"
endef




# Launch a command in the specified container and user
# Ex: $(call docker_container_exec, <CONTAINER_NAME>, <USER>, <COMMAND>)
define docker_container_exec
	$(call docker-text,$(1) >$(3))
	$(eval COMMAND_TO_LAUNCH_IN_CONTAINER := $(3))
	if [ "$(COMMAND_TO_LAUNCH_IN_CONTAINER)" != "" ]; then \
		docker exec -it -u $(2) $(1) bash -c "$(COMMAND_TO_LAUNCH_IN_CONTAINER)"; \
	else \
			docker exec -it -u $(2) $(1) bash; \
	fi
endef

# Wait a specified resource
# Ex: $(call docker_wait,tcp,db_container,3306)
define docker_wait
	$(call docker-text, Waiting $(1)://$(2):$(3))
	docker run --network="container:$(2)" --rm jwilder/dockerize -wait $(1)://$(2):$(3)
endef

##
## Docker
## -------
##

docker-compose-up: ## Up docker compose stack
	$(DOCKER_COMPOSE) up -d --remove-orphans --no-recreate

docker-compose-stop: ## Stop docker compose stack
	$(DOCKER_COMPOSE) stop

docker-compose-down: ## Down docker compose stack
	$(DOCKER_COMPOSE) down

docker-compose-build: ## Build docker compose stack
	$(DOCKER_COMPOSE) build --pull

docker-compose-kill: ## Kill docker compose stack
	$(DOCKER_COMPOSE) kill

docker-compose-clean: ## Clean docker compose stack
	$(DOCKER_COMPOSE) down --volumes --remove-orphans
