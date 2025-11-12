#!/bin/bash

# Script otomatis untuk push SIPAKRT ke GitHub
# Usage: ./push-to-github.sh

set -e

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║          SIPAKRT - Push to GitHub Repository                ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if git is installed
if ! command -v git &> /dev/null; then
    echo -e "${RED}❌ Git not found! Please install git first.${NC}"
    exit 1
fi

echo "📝 Git Configuration"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Check git config
GIT_NAME=$(git config --global user.name 2>/dev/null || echo "")
GIT_EMAIL=$(git config --global user.email 2>/dev/null || echo "")

if [ -z "$GIT_NAME" ] || [ -z "$GIT_EMAIL" ]; then
    echo -e "${YELLOW}⚠️  Git config not set. Let's configure it:${NC}"
    echo ""
    
    read -p "Your Name: " input_name
    read -p "Your Email: " input_email
    
    git config --global user.name "$input_name"
    git config --global user.email "$input_email"
    
    echo -e "${GREEN}✓ Git configured!${NC}"
else
    echo -e "${GREEN}✓ Git already configured:${NC}"
    echo "  Name:  $GIT_NAME"
    echo "  Email: $GIT_EMAIL"
fi

echo ""
echo "🧹 Cleaning up temporary files..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
rm -f *.sql *.tar.gz *.zip backup-* 2>/dev/null || true
echo -e "${GREEN}✓ Cleaned${NC}"

echo ""
echo "🔍 Verifying sensitive files are ignored..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Check .env is ignored
if grep -q "^\.env$" .gitignore; then
    echo -e "${GREEN}✓ .env is in .gitignore${NC}"
else
    echo -e "${RED}❌ .env not in .gitignore! Adding...${NC}"
    echo ".env" >> .gitignore
fi

# Check vendor is ignored
if grep -q "^/vendor" .gitignore; then
    echo -e "${GREEN}✓ vendor/ is in .gitignore${NC}"
else
    echo -e "${RED}❌ vendor/ not in .gitignore! Adding...${NC}"
    echo "/vendor/" >> .gitignore
fi

echo ""
echo "🔧 Initializing Git repository..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ -d .git ]; then
    echo -e "${YELLOW}⚠️  Git already initialized${NC}"
else
    git init
    echo -e "${GREEN}✓ Git initialized${NC}"
fi

echo ""
echo "📦 Adding files to git..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
git add .

# Show what will be committed
echo ""
echo "Files to be committed:"
git status --short | head -20
TOTAL_FILES=$(git status --short | wc -l)
if [ $TOTAL_FILES -gt 20 ]; then
    echo "... and $((TOTAL_FILES - 20)) more files"
fi

echo ""
echo -e "${YELLOW}⚠️  IMPORTANT: Verify .env is NOT in the list above!${NC}"
echo ""
read -p "Continue with commit? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 1
fi

echo ""
echo "💾 Committing files..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

git commit -m "feat: initial commit - SIPAKRT v1.0.1

- Sistem Informasi Pendataan Anggota Kartu Keluarga RT
- Laravel 10 with Filament Admin Panel v2.17
- Household (KK) management with auto-status update
- Resident management with auto-sync head resident
- RT management with officials tracking
- Excel export with advanced filters (RT, Status)
- Complete observers (HouseholdObserver, ResidentObserver)
- Docker support with docker-compose
- Production-ready deployment configs
- Complete documentation (README, DEPLOYMENT, CONTRIBUTING)
- GitHub Actions CI/CD workflows (Laravel tests, Docker build)
- Quick installation script for regular and Docker setup

Features:
✨ Auto-create kepala keluarga saat KK dibuat
✨ Auto-update status KK jika kepala meninggal/cerai  
✨ View page (read-only) untuk detail KK
✨ Export Excel dengan filter RT dan status
✨ Multi-user support dengan role management
✨ Repair script untuk fix data lama

Tech Stack:
- PHP 8.1+
- Laravel 10.49.1
- Filament 2.17.58
- MySQL 8.0
- Maatwebsite/Excel 3.1.59"

echo -e "${GREEN}✓ Committed successfully!${NC}"

echo ""
echo "🌐 GitHub Repository Setup"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📋 Repository Info:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Repository Name: sipakrt"
echo "Description:     Sistem Informasi Pendataan Anggota Kartu Keluarga RT"
echo "Topics:          laravel, filament, php, mysql, docker, household-management"
echo "License:         MIT"
echo ""

echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}  PLEASE CREATE GITHUB REPOSITORY FIRST!${NC}"
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"
echo ""
echo "1. Go to: https://github.com/new"
echo "2. Repository name: ${GREEN}sipakrt${NC}"
echo "3. Description: ${GREEN}Sistem Informasi Pendataan Anggota Kartu Keluarga RT${NC}"
echo "4. Choose: Public or Private"
echo "5. ❌ DO NOT check 'Initialize this repository with a README'"
echo "6. Click 'Create repository'"
echo ""

read -p "Press ENTER when repository is created..." 

echo ""
echo "🔗 Connect to GitHub Remote"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Choose authentication method:"
echo "  1) SSH (recommended if you have SSH key setup)"
echo "  2) HTTPS (requires Personal Access Token)"
echo ""
read -p "Choose (1 or 2): " -n 1 -r AUTH_METHOD
echo ""

read -p "Your GitHub username: " GITHUB_USER

if [ "$AUTH_METHOD" = "1" ]; then
    REMOTE_URL="git@github.com:$GITHUB_USER/sipakrt.git"
    echo ""
    echo "Using SSH: $REMOTE_URL"
    
    # Test SSH connection
    echo "Testing SSH connection..."
    if ssh -T git@github.com 2>&1 | grep -q "successfully authenticated"; then
        echo -e "${GREEN}✓ SSH connection successful${NC}"
    else
        echo -e "${YELLOW}⚠️  SSH test returned non-success. Continuing anyway...${NC}"
        echo "If push fails, you may need to setup SSH key:"
        echo "  https://docs.github.com/en/authentication/connecting-to-github-with-ssh"
    fi
else
    REMOTE_URL="https://github.com/$GITHUB_USER/sipakrt.git"
    echo ""
    echo "Using HTTPS: $REMOTE_URL"
    echo ""
    echo -e "${YELLOW}Note: You will need Personal Access Token (not password)${NC}"
    echo "Create token at: https://github.com/settings/tokens"
fi

echo ""
echo "Adding remote origin..."

# Remove origin if exists
git remote remove origin 2>/dev/null || true

git remote add origin "$REMOTE_URL"
echo -e "${GREEN}✓ Remote added${NC}"

echo ""
echo "🚀 Pushing to GitHub..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

git branch -M main

echo ""
echo "Pushing to main branch..."
if git push -u origin main; then
    echo ""
    echo -e "${GREEN}╔══════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║                  🎉 SUCCESS! 🎉                              ║${NC}"
    echo -e "${GREEN}╚══════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo "✅ SIPAKRT successfully pushed to GitHub!"
    echo ""
    echo "🌐 View your repository at:"
    echo "   ${GREEN}https://github.com/$GITHUB_USER/sipakrt${NC}"
    echo ""
    echo "📋 Next Steps:"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "1. Add repository description and topics"
    echo "2. Setup branch protection rules (Settings → Branches)"
    echo "3. Enable Dependabot (Settings → Security)"
    echo "4. Check GitHub Actions workflows"
    echo "5. Create first release (Releases → Create new release)"
    echo "6. Add collaborators if needed"
    echo "7. Star your own repository! ⭐"
    echo ""
    echo "📖 Documentation:"
    echo "   README.md           - Main documentation"
    echo "   DEPLOYMENT.md       - Production deployment guide"
    echo "   CONTRIBUTING.md     - Contributing guidelines"
    echo "   GITHUB_DEPLOYMENT.md - GitHub detailed guide"
    echo ""
else
    echo ""
    echo -e "${RED}╔══════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${RED}║                  ❌ PUSH FAILED ❌                           ║${NC}"
    echo -e "${RED}╚══════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo "Common issues:"
    echo "1. Repository not created on GitHub"
    echo "2. SSH key not setup (if using SSH)"
    echo "3. Wrong username"
    echo "4. Need Personal Access Token (if using HTTPS)"
    echo ""
    echo "📖 See GITHUB_DEPLOYMENT.md for detailed troubleshooting"
    exit 1
fi
