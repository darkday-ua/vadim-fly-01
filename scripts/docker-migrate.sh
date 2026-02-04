#!/bin/bash

set -e

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
ENV_FILE="$PROJECT_ROOT/.env"

# Check if .env exists
if [ ! -f "$ENV_FILE" ]; then
    echo "❌ Error: .env file not found at $ENV_FILE"
    echo "   Create it from .env.example: cp .env.example .env"
    exit 1
fi

# Load .env file
set -a
source "$ENV_FILE"
set +a

# Required environment variables
REQUIRED_VARS=(
    "DB_HOST"
    "DB_PORT"
    "DB_NAME"
    "DB_USER"
    "DB_PASSWORD"
    "DEFAULT_USERNAME"
    "DEFAULT_PASSWORD"
)

# Validate required variables
MISSING_VARS=()
for var in "${REQUIRED_VARS[@]}"; do
    if [ -z "${!var}" ]; then
        MISSING_VARS+=("$var")
    fi
done

if [ ${#MISSING_VARS[@]} -ne 0 ]; then
    echo "❌ Error: Missing required environment variables in .env:"
    for var in "${MISSING_VARS[@]}"; do
        echo "   - $var"
    done
    echo ""
    echo "   Please set these in $ENV_FILE"
    exit 1
fi

# Detect docker compose command (v2 uses "docker compose", v1 uses "docker-compose")
if command -v docker &> /dev/null && docker compose version &> /dev/null; then
    DOCKER_COMPOSE="docker compose"
elif command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE="docker-compose"
else
    echo "❌ Error: Neither 'docker compose' nor 'docker-compose' found"
    exit 1
fi

echo "🔍 Running migrations..."
$DOCKER_COMPOSE exec -T app php scripts/migrate.php

echo ""
echo "👤 Seeding default user..."
$DOCKER_COMPOSE exec -T app php scripts/seed.php

echo ""
echo "✅ Done!"
