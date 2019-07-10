include docker.mk

.PHONY: test run stop clean distro prod

DRUPAL_VER ?= 7
PHP_VER ?= 7.2

test:
	cd ./tests/$(DRUPAL_VER) && PHP_VER=$(PHP_VER) ./run.sh

run:
	docker-compose run --rm -p 8080:80 dev_pece

in:
	docker exec -it $(shell docker-compose ps | grep _dev_ | cut -d" " -f 1) /bin/bash

clean:
	docker-compose down
	rm -rf ./node_modules
	rm -rf ./cnf
	rm -rf ./builds
	rm -rf ./build

distro-clean:
	docker-compose down
	rm -rf ./build

# prod:
# 	docker-compose run --rm -p 8080:80 production

# distro: distro-clean
# 	docker-compose run --rm production gulp pack-distro
