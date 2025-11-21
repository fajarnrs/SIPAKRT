#!/bin/bash

# Quick Start Script untuk Data Warga
# Usage: ./quick-start.sh [regular|docker]

set -e

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║        DATA WARGA - Quick Start Installation                ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

MODE=${1:-regular}

if [ "$MODE" = "docker" ]; then
    echo "🐳 Starting Docker installation..."
    
    # Check Docker
    if ! command -v docker &> /dev/null; then
        echo "❌ Docker not found! Please install Docker first."
        exit 1
    fi
    
    # Copy environment
    if [ ! -f .env ]; then
        echo "📝 Creating .env file..."
        cp .env.example .env
        echo "⚠️  Please edit .env file with your database credentials!"
        read -p "Press enter when ready..."
    fi
    
    # Build and start
    echo "🔨 Building Docker containers..."
    docker-compose up -d --build
    
    # Install dependencies
    echo "📦 Installing dependencies..."
    docker-compose exec app composer install
    
    # Generate key
    echo "🔑 Generating application key..."
    docker-compose exec app php artisan key:generate
    
    # Storage link
    echo "🔗 Creating storage link..."
    docker-compose exec app php artisan storage:link
    
    # Run migrations
    echo "📊 Running migrations..."
    docker-compose exec app php artisan migrate
    
    # Create admin
    echo ""
    echo "👤 Creating admin user..."
    docker-compose exec app php artisan make:filament-user
    
    echo ""
    echo "✅ Docker installation complete!"
    echo ""
    echo "🌐 Access your application:"
    echo "   App:        http://localhost:8000"
    echo "   Admin:      http://localhost:8000/admin"
    echo "   phpMyAdmin: http://localhost:8080"
    echo ""
    
elif [ "$MODE" = "regular" ]; then
    echo "⚙️  Starting regular installation..."
    
    # Check PHP
    if ! command -v php &> /dev/null; then
        echo "❌ PHP not found! Please install PHP 8.1+ first."
        exit 1
    fi
    
    # Check Composer
    if ! command -v composer &> /dev/null; then
        echo "❌ Composer not found! Please install Composer first."
        exit 1
    fi
    
    # Check MySQL
    if ! command -v mysql &> /dev/null; then
        echo "⚠️  MySQL command not found. Make sure MySQL is installed."
    fi
    
    # Install dependencies
    echo "📦 Installing dependencies..."
    composer install
    
    # Copy environment
    if [ ! -f .env ]; then
        echo "📝 Creating .env file..."
        cp .env.example .env
        php artisan key:generate
        echo ""
        echo "⚠️  Please edit .env file with your database credentials!"
        echo "   Database: pendataan_warga"
        read -p "Press enter when database is configured..."
    fi
    
    # Storage link
    echo "🔗 Creating storage link..."
    php artisan storage:link
    
    # Set permissions
    echo "🔒 Setting permissions..."
    chmod -R 775 storage bootstrap/cache
    
    # Run migrations
    echo "📊 Running migrations..."
    read -p "Have you created the database? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan migrate
    else
        echo "Please create database first:"
        echo "  mysql -u root -p -e \"CREATE DATABASE pendataan_warga CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\""
        exit 1
    fi
    
    # Create admin
    echo ""
    echo "👤 Creating admin user..."
    php artisan make:filament-user
    
    echo ""
    echo "✅ Installation complete!"
    echo ""
    echo "🚀 Start the application:"
    echo "   php artisan serve"
    echo ""
    echo "🌐 Access your application:"
    echo "   App:   http://localhost:8000"
    echo "   Admin: http://localhost:8000/admin"
    echo ""
    
else
    echo "❌ Invalid mode: $MODE"
    echo ""
    echo "Usage: ./quick-start.sh [regular|docker]"
    echo ""
    echo "Examples:"
    echo "  ./quick-start.sh regular  # Regular installation"
    echo "  ./quick-start.sh docker   # Docker installation"
    exit 1
fi

echo "📚 For more information, see README.md"
echo ""
