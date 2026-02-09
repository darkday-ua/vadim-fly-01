.PHONY: build push build-push help

# Default tag if not provided
TAG ?= latest

# Docker image name
IMAGE = darkday443/vadim-fly

help:
	@echo "Usage: make <target> [TAG=<tag>]"
	@echo ""
	@echo "Targets:"
	@echo "  build       Build Docker image (default tag: latest)"
	@echo "  push        Push Docker image to registry"
	@echo "  build-push  Build and push in one command"
	@echo "  help        Show this help message"
	@echo ""
	@echo "Examples:"
	@echo "  make build TAG=v1.0.0"
	@echo "  make push TAG=v1.0.0"
	@echo "  make build-push TAG=v1.0.0"

build:
	@echo "Building $(IMAGE):$(TAG)..."
	docker build -t $(IMAGE):$(TAG) .
	@echo "✅ Build complete: $(IMAGE):$(TAG)"

push:
	@echo "Pushing $(IMAGE):$(TAG)..."
	docker push $(IMAGE):$(TAG)
	@echo "✅ Push complete: $(IMAGE):$(TAG)"

build-push: build push
	@echo "✅ Build and push complete: $(IMAGE):$(TAG)"
