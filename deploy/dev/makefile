.PHONY: all deps refresh-deps php down up clean-deps

all: deps php down up

# Acquire the pinned ThirtyBees source and submodules into the ignored
# src/thirtybees/www/thirtybees directory. Re-running this target is cheap
# when the recorded commit already matches.
deps:
	bash ../../src/thirtybees/fetch-thirtybees.sh --if-missing

# Force a fresh fetch after changing the pinned commit or the overlay.
refresh-deps:
	bash ../../src/thirtybees/fetch-thirtybees.sh

php: deps
	docker build --progress=plain --platform=amd64 --tag "ghcr.io/wilsonify/thirtybees:1.0.0" \
		-f ../../src/thirtybees/Dockerfile ../../src/thirtybees

down:
	docker-compose down

up: deps down
	docker-compose up -d

clean-deps:
	rm -rf ../../src/thirtybees/www/thirtybees
