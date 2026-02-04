#!/bin/bash

set -e

echo "🐳 Fly Docker Setup"
echo "==================="
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env from .env.example..."
    cp .env.example .env
    echo "✅ Created .env file"
    echo ""
    echo "⚠️  Please edit .env and configure required variables:"
    echo "   - DB_HOST=mysql (for Docker)"
    echo "   - DB_NAME, DB_USER, DB_PASSWORD"
    echo "   - DEFAULT_USERNAME, DEFAULT_PASSWORD"
    echo ""
    read -p "Press Enter to continue after editing .env..."
fi

# Load .env file
set -a
source .env
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
    fi
    echo ""
    echo "   Please set these in .env file"
    exit 1
fi

# Check if DB_HOST is set to mysql (recommended for Docker)
if [ "$DB_HOST" != "mysql" ]; then
    echo "⚠️  Warning: DB_HOST is set to '$DB_HOST'"
    echo "   For Docker, DB_HOST should be 'mysql' (the service name)"
    echo "   Current value will be used, but may cause connection issues"
    echo ""
fi

echo "🔨 Building Docker containers..."
docker-compose build

echo ""
echo "🚀 Starting containers..."
docker-compose up -d

echo ""
echo "⏳ Waiting for MySQL to be ready..."
sleep 5

echo ""
echo "📊 Running migrations..."
docker-compose exec -T app php scripts/migrate.php

echo ""
echo "👤 Seeding default user..."
docker-compose exec -T app php scripts/seed.php

echo ""
echo "✅ Setup complete!"
echo ""
echo "🌐 App is available at: http://localhost:8080"
echo ""
echo "Useful commands:"
echo "  docker-compose logs -f app    # View app logs"
echo "  docker-compose logs -f mysql   # View MySQL logs"
echo "  docker-compose down            # Stop containers"
echo "  docker-compose exec app bash   # Access app container"
echo ""
