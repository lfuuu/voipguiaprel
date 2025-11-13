#!/bin/bash

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== VoIP GUI Development Server Setup ===${NC}"

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo -e "${RED}Error: composer is not installed. Please install composer first.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Composer found${NC}"

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo -e "${RED}Error: PHP is not installed. Please install PHP 7.4+ first.${NC}"
    exit 1
fi

PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo -e "${GREEN}✓ PHP found (version: $PHP_VERSION)${NC}"

# Install composer dependencies
if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}Installing composer dependencies...${NC}"
    # Try to install with platform requirements ignored for compatibility
    if ! composer install --ignore-platform-req=ext-gd --ignore-platform-req=php 2>/dev/null; then
        echo -e "${YELLOW}Lock file issues detected, running composer update...${NC}"
        composer update --ignore-platform-req=ext-gd --ignore-platform-req=php --with-all-dependencies
    fi
    echo -e "${GREEN}✓ Composer dependencies installed${NC}"
else
    echo -e "${GREEN}✓ Composer dependencies already installed${NC}"
fi

# Setup directories and permissions
echo -e "${YELLOW}Setting up directories and permissions...${NC}"
mkdir -p runtime web/assets
chmod -R 777 runtime web/assets 2>/dev/null || chmod -R 775 runtime web/assets
echo -e "${GREEN}✓ Directories and permissions configured${NC}"

# Copy configuration templates if they don't exist
echo -e "${YELLOW}Setting up configuration files...${NC}"

if [ ! -f "config/db-local.php" ]; then
    cp config/db-local.tpl.php config/db-local.php
    # Update database configuration
    sed -i "s|'dsn' => 'pgsql:host=localhost;dbname=nispd'|'dsn' => 'pgsql:host=localhost;dbname=nispd_test;port=5432'|" config/db-local.php
    sed -i "s|'username' => ''|'username' => 'voipgui'|" config/db-local.php
    sed -i "s|'password' => ''|'password' => 'qwe123'|" config/db-local.php
    echo -e "${GREEN}✓ Created config/db-local.php${NC}"
else
    echo -e "${GREEN}✓ config/db-local.php already exists${NC}"
fi

if [ ! -f "config/log-local.php" ]; then
    cp config/log-local.tpl.php config/log-local.php
    echo -e "${GREEN}✓ Created config/log-local.php${NC}"
else
    echo -e "${GREEN}✓ config/log-local.php already exists${NC}"
fi

if [ ! -f "config/params-local.php" ]; then
    cp config/params-local.tpl.php config/params-local.php
    echo -e "${GREEN}✓ Created config/params-local.php${NC}"
else
    echo -e "${GREEN}✓ config/params-local.php already exists${NC}"
fi

# Start PostgreSQL with docker-compose
echo -e "${YELLOW}Starting PostgreSQL with docker-compose...${NC}"

if ! command -v docker-compose &> /dev/null && ! command -v docker &> /dev/null; then
    echo -e "${RED}Error: docker-compose or docker is not installed.${NC}"
    exit 1
fi

# Use docker compose (newer) or docker-compose (older)
if command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker-compose"
else
    DOCKER_COMPOSE_CMD="docker compose"
fi

$DOCKER_COMPOSE_CMD up -d postgres

echo -e "${YELLOW}Waiting for PostgreSQL to be ready...${NC}"

# Wait for PostgreSQL to be ready
MAX_ATTEMPTS=30
ATTEMPT=0
while [ $ATTEMPT -lt $MAX_ATTEMPTS ]; do
    if $DOCKER_COMPOSE_CMD exec -T postgres pg_isready -U postgres > /dev/null 2>&1; then
        echo -e "${GREEN}✓ PostgreSQL is ready${NC}"
        break
    fi
    ATTEMPT=$((ATTEMPT + 1))
    echo -n "."
    sleep 1
done

if [ $ATTEMPT -eq $MAX_ATTEMPTS ]; then
    echo -e "\n${RED}Error: PostgreSQL did not become ready in time${NC}"
    exit 1
fi

# Wait a bit more for the database to be fully initialized
sleep 2

# Start PHP built-in server
echo -e "${GREEN}=== Starting PHP Development Server ===${NC}"
echo -e "${GREEN}Server will be available at: http://localhost:8000${NC}"
echo -e "${YELLOW}Press Ctrl+C to stop the server${NC}"
echo ""

cd "$(dirname "$0")"
php -S localhost:8000 -t web

