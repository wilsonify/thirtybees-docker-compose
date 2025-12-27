.PHONY: cluster-create cluster-delete deploy undeploy logs status secret port-forward clean all sops-operator sops-age-secret load-image encrypt-secrets

CLUSTER_NAME := thirtybees-prod
NAMESPACE := thirtybees-prod
CONTEXT := kind-$(CLUSTER_NAME)
AGE_PUBLIC_KEY := age1mg4zlx7p736nnrp7glt7gyd96s33kmy8wlck903m0srkkndeaawqrqfzek
THIRTYBEES_IMAGE := ghcr.io/wilsonify/thirtybees:latest

# Default target
all: cluster-create load-image sops-operator sops-age-secret secret deploy status

# Create KIND cluster
cluster-create:
	@echo "Creating KIND cluster: $(CLUSTER_NAME)"
	@kind get clusters | grep -q $(CLUSTER_NAME) || kind create cluster --config kind-config.yaml
	@kubectl config use-context $(CONTEXT)
	@echo "Cluster $(CLUSTER_NAME) is ready"

# Load Docker image into KIND cluster
load-image:
	@echo "Loading $(THIRTYBEES_IMAGE) into KIND cluster..."
	@if docker image inspect $(THIRTYBEES_IMAGE) >/dev/null 2>&1; then \
		kind load docker-image $(THIRTYBEES_IMAGE) --name $(CLUSTER_NAME); \
	else \
		echo "WARNING: Image $(THIRTYBEES_IMAGE) not found locally. Build it first with:"; \
		echo "  cd ../../src/thirtybees && docker build -t $(THIRTYBEES_IMAGE) ."; \
	fi

# Install SOPS Secrets Operator
sops-operator:
	@echo "Installing SOPS Secrets Operator..."
	@kubectl --context $(CONTEXT) apply -f https://raw.githubusercontent.com/isindir/sops-secrets-operator/master/config/crd/bases/isindir.github.com_sopssecrets.yaml || true
	@helm repo add sops-secrets-operator https://isindir.github.io/sops-secrets-operator/ 2>/dev/null || true
	@helm repo update
	@helm upgrade --install sops-secrets-operator sops-secrets-operator/sops-secrets-operator \
		--namespace sops --create-namespace \
		--set secretsAsFiles[0].name=sops-age \
		--set secretsAsFiles[0].mountPath=/etc/sops-age \
		--set secretsAsFiles[0].secretName=sops-age \
		--set envVars[0].name=SOPS_AGE_KEY_FILE \
		--set envVars[0].value=/etc/sops-age/keys.txt \
		--wait --timeout=120s || echo "Helm not available, install operator manually"

# Create age key secret for SOPS operator (requires AGE_SECRET_KEY env var)
sops-age-secret:
	@echo "Creating SOPS age key secret..."
	@if [ -n "$$AGE_SECRET_KEY" ]; then \
		kubectl --context $(CONTEXT) create namespace sops --dry-run=client -o yaml | kubectl apply -f -; \
		kubectl --context $(CONTEXT) -n sops delete secret sops-age --ignore-not-found; \
		echo "$$AGE_SECRET_KEY" | kubectl --context $(CONTEXT) -n sops create secret generic sops-age \
			--from-file=keys.txt=/dev/stdin; \
		echo "SOPS age secret created"; \
	else \
		echo "WARNING: AGE_SECRET_KEY not set. Set it with: export AGE_SECRET_KEY=\$$(cat ~/.sops/age/keys.txt)"; \
	fi

# Encrypt secrets with SOPS
encrypt-secrets:
	@echo "Encrypting secrets..."
	@cd base && sops encrypt --age $(AGE_PUBLIC_KEY) --encrypted-regex "PASSWORD|EMAIL|TOKEN" thirtybees-secrets.dec.yaml > thirtybees-secrets.enc.yaml
	@echo "Secrets encrypted to base/thirtybees-secrets.enc.yaml"

# Delete KIND cluster
cluster-delete:
	@echo "Deleting KIND cluster: $(CLUSTER_NAME)"
	@kind delete cluster --name $(CLUSTER_NAME) 2>/dev/null || true

# Create secret from .env file (run this before deploy)
secret:
	@echo "Creating cloudflared secret..."
	@if [ -f .env ]; then \
		kubectl --context $(CONTEXT) create namespace $(NAMESPACE) --dry-run=client -o yaml | kubectl apply -f -; \
		kubectl --context $(CONTEXT) -n $(NAMESPACE) delete secret cloudflared-secret --ignore-not-found; \
		kubectl --context $(CONTEXT) -n $(NAMESPACE) create secret generic cloudflared-secret \
			--from-env-file=.env; \
	else \
		echo "ERROR: .env file not found. Create .env with CLOUD_FLARE_TOKEN=<your-token>"; \
		exit 1; \
	fi

# Deploy using kustomize
deploy:
	@echo "Deploying to $(NAMESPACE)..."
	@kubectl --context $(CONTEXT) apply -k base/
	@echo "Waiting for deployments to be ready..."
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) rollout status deployment/php --timeout=120s
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) rollout status deployment/nginx --timeout=60s
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) rollout status deployment/cloudflared --timeout=60s

# Remove deployment
undeploy:
	@echo "Removing deployment from $(NAMESPACE)..."
	@kubectl --context $(CONTEXT) delete -k base/ --ignore-not-found 2>/dev/null || true

# View logs
logs:
	@echo "=== PHP Logs ==="
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) logs -l app.kubernetes.io/component=php --tail=20
	@echo "\n=== Nginx Logs ==="
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) logs -l app.kubernetes.io/component=nginx --tail=20
	@echo "\n=== Cloudflared Logs ==="
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) logs -l app.kubernetes.io/component=cloudflared --tail=20

logs-php:
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) logs -f -l app.kubernetes.io/component=php

logs-nginx:
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) logs -f -l app.kubernetes.io/component=nginx

logs-cloudflared:
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) logs -f -l app.kubernetes.io/component=cloudflared

# Check status
status:
	@echo "=== Cluster Info ==="
	@kubectl --context $(CONTEXT) cluster-info
	@echo "\n=== Namespace: $(NAMESPACE) ==="
	@echo "\n--- Pods ---"
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) get pods -o wide
	@echo "\n--- Services ---"
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) get services
	@echo "\n--- ConfigMaps ---"
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) get configmaps
	@echo "\n--- Secrets ---"
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) get secrets

# Port forward to local machine
port-forward:
	@echo "Port forwarding nginx to localhost:8082..."
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) port-forward svc/nginx 8082:80

# Clean up everything
clean: undeploy cluster-delete
	@echo "Cleanup complete"

# Test database connection
test-db:
	@echo "Testing Neon database connection..."
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) exec -it $$(kubectl --context $(CONTEXT) -n $(NAMESPACE) get pod -l app.kubernetes.io/component=php -o jsonpath='{.items[0].metadata.name}') -- php -r "echo 'DB connection test placeholder';"

# Show database config
show-db-config:
	@echo "=== Database Configuration ==="
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) get configmap thirtybees-config -o yaml | grep -E "DB_SERVER|DB_USERNAME|DB_DATABASE|DB_SSLMODE"

# Restart deployments
restart:
	@echo "Restarting deployments..."
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) rollout restart deployment/php
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) rollout restart deployment/nginx
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) rollout restart deployment/cloudflared
