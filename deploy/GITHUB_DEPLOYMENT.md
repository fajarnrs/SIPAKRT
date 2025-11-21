# 📤 GitHub Deployment Guide

Panduan lengkap untuk mempublikasikan Data Warga ke GitHub repository.

## 🎯 Prerequisites

- Git installed
- GitHub account
- SSH key configured (recommended) atau Personal Access Token

## 📋 Step-by-Step Guide

### 1. Buat Repository Baru di GitHub

1. Login ke GitHub
2. Klik tombol "New repository" atau pergi ke: https://github.com/new
3. Isi detail repository:
   - **Repository name**: `data-warga`
   - **Description**: "Sistem Pendataan Warga - Laravel & Filament Admin Panel"
   - **Visibility**: Public (atau Private sesuai kebutuhan)
   - **❌ JANGAN** centang "Initialize this repository with a README"
4. Klik "Create repository"

### 2. Prepare Local Repository

```bash
cd /opt/data-warga

# Initialize git (jika belum)
git init

# Check current status
git status
```

### 3. Clean Up Sensitive Files

**PENTING!** Pastikan file-file ini **TIDAK** masuk ke repository:

```bash
# Cek .gitignore sudah benar
cat .gitignore

# Remove sensitive files dari tracking (jika ada)
git rm --cached .env
git rm --cached -r storage/logs/
git rm --cached -r node_modules/
git rm --cached -r vendor/

# Hapus backup files
rm -f *.sql *.tar.gz *.zip backup-*
rm -f README.old.md
```

### 4. Add & Commit Files

```bash
# Add all files
git add .

# Check what will be committed
git status

# Commit
git commit -m "feat: initial commit - Data Warga v1.0.1

- Laravel 10 with Filament Admin Panel
- Household (KK) management with auto-status update
- Resident management with auto-sync head resident
- RT management
- Excel export with filters
- Docker support
- Complete documentation"
```

### 5. Connect to GitHub Remote

Ganti `USERNAME` dengan GitHub username Anda:

```bash
# Add remote (HTTPS)
git remote add origin https://github.com/USERNAME/data-warga.git

# Or menggunakan SSH (recommended)
git remote add origin git@github.com:USERNAME/data-warga.git

# Verify remote
git remote -v
```

### 6. Push to GitHub

```bash
# Push ke main branch
git branch -M main
git push -u origin main
```

Jika diminta kredensial:
- **HTTPS**: Masukkan GitHub username dan Personal Access Token (bukan password)
- **SSH**: Pastikan SSH key sudah di-setup

### 7. Verify di GitHub

1. Buka repository Anda: `https://github.com/USERNAME/data-warga`
2. Pastikan semua file ter-upload
3. Cek README.md tampil dengan baik
4. Verify `.env` **TIDAK** ada di repository

---

## 🔐 Setup SSH Key (Recommended)

### Generate SSH Key

```bash
# Generate new SSH key
ssh-keygen -t ed25519 -C "your-email@example.com"

# Start ssh-agent
eval "$(ssh-agent -s)"

# Add key to ssh-agent
ssh-add ~/.ssh/id_ed25519

# Copy public key
cat ~/.ssh/id_ed25519.pub
```

### Add to GitHub

1. Copy output dari command di atas
2. Pergi ke GitHub → Settings → SSH and GPG keys
3. Klik "New SSH key"
4. Paste public key
5. Save

### Test Connection

```bash
ssh -T git@github.com
# Should output: "Hi USERNAME! You've successfully authenticated..."
```

---

## 📝 Create Personal Access Token (For HTTPS)

1. GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Click "Generate new token (classic)"
3. Set name: "data-warga-deployment"
4. Set expiration
5. Select scopes:
   - ✅ `repo` (full control)
   - ✅ `workflow` (for GitHub Actions)
6. Click "Generate token"
7. **COPY token** (you won't see it again!)
8. Use token as password when git asks

---

## 🌿 Branching Strategy

### Main Branch

```bash
# Main branch untuk production-ready code
git checkout main
git pull origin main
```

### Development Branch

```bash
# Create develop branch
git checkout -b develop
git push -u origin develop
```

### Feature Branch

```bash
# Create feature branch from develop
git checkout develop
git checkout -b feature/nama-fitur

# Work on feature...
git add .
git commit -m "feat: implement new feature"

# Push feature branch
git push -u origin feature/nama-fitur
```

### Create Pull Request

1. Pergi ke GitHub repository
2. Klik "Pull requests" → "New pull request"
3. Base: `develop` ← Compare: `feature/nama-fitur`
4. Add description
5. Create pull request
6. Wait for review & merge

---

## 🔄 Update Repository

### Push Changes

```bash
# Check status
git status

# Add changes
git add .

# Commit dengan conventional commits
git commit -m "fix: resolve household observer issue"

# Push
git push origin main
```

### Pull Changes

```bash
# Get latest changes
git pull origin main

# Or fetch first
git fetch origin
git merge origin/main
```

---

## 🏷️ Tagging Releases

```bash
# Create annotated tag
git tag -a v1.0.1 -m "Release v1.0.1

- Fixed household observer bug
- Added export Excel feature
- Updated documentation"

# Push tag
git push origin v1.0.1

# Push all tags
git push origin --tags
```

### Create GitHub Release

1. Go to repository → Releases
2. Click "Create a new release"
3. Select tag: `v1.0.1`
4. Fill release notes
5. Attach files (optional): deployment package
6. Publish release

---

## 📦 .gitignore Important Files

Pastikan `.gitignore` mengandung:

```gitignore
# Laravel
/vendor/
/node_modules/
.env
.env.backup
.env.production
/storage/*.key
/storage/logs/*

# Deployment
*.sql
*.tar.gz
*.zip
backup-*
deploy.sh

# IDE
.idea/
.vscode/
*.swp

# OS
.DS_Store
Thumbs.db
```

---

## ⚠️ NEVER Commit These Files

- ❌ `.env` file (database credentials!)
- ❌ `vendor/` folder (too large, use composer)
- ❌ `node_modules/` folder (use npm/yarn)
- ❌ Database dumps (`.sql` files)
- ❌ Backup archives (`.tar.gz`, `.zip`)
- ❌ API keys or secrets
- ❌ Private keys (`.pem`, `.key`)
- ❌ Log files

---

## 🔍 Verify Before Push

```bash
# Check what will be pushed
git diff origin/main

# Check files to be committed
git status

# Check gitignore is working
git check-ignore -v vendor/ .env node_modules/

# Test if .env is ignored
git add .env
# Should output: "The following paths are ignored..."
```

---

## 🚀 GitHub Actions Setup

GitHub Actions akan otomatis run setelah push:

### View Workflow Status

1. Repository → Actions tab
2. See running workflows
3. Click workflow untuk detail logs

### Disable/Enable Workflows

1. Actions tab → Select workflow
2. Click "..." → Disable/Enable workflow

---

## 📊 Repository Settings

### Recommended Settings

1. **Settings → General**
   - ✅ Issues
   - ✅ Pull Requests
   - ✅ Discussions (optional)

2. **Settings → Branches**
   - Add branch protection rule untuk `main`:
     - ✅ Require pull request before merging
     - ✅ Require status checks to pass (CI tests)
     - ❌ Allow force pushes (dangerous!)

3. **Settings → Security**
   - ✅ Enable Dependabot alerts
   - ✅ Enable Dependabot security updates

---

## 🆘 Common Issues

### Issue: Permission denied (publickey)

```bash
# Solution: Add SSH key to GitHub (see SSH setup above)
```

### Issue: Remote already exists

```bash
# Remove old remote
git remote remove origin

# Add correct remote
git remote add origin git@github.com:USERNAME/data-warga.git
```

### Issue: Accidentally committed .env

```bash
# Remove from git history
git rm --cached .env
git commit -m "chore: remove .env from tracking"

# Change all passwords in .env!
# Update .gitignore to include .env
echo ".env" >> .gitignore
git add .gitignore
git commit -m "chore: update gitignore"
git push origin main
```

### Issue: Large files error

```bash
# GitHub has 100MB file limit
# Remove large files or use Git LFS

# Check large files
find . -size +50M -not -path "./vendor/*" -not -path "./node_modules/*"

# Use Git LFS for large files
git lfs install
git lfs track "*.sql"
git add .gitattributes
```

---

## 📚 Additional Resources

- [GitHub Docs](https://docs.github.com)
- [Git Cheat Sheet](https://education.github.com/git-cheat-sheet-education.pdf)
- [Conventional Commits](https://www.conventionalcommits.org/)

---

## ✅ Post-Deployment Checklist

- [ ] Repository created on GitHub
- [ ] All files pushed successfully
- [ ] `.env` not in repository
- [ ] README.md displays correctly
- [ ] GitHub Actions workflows running
- [ ] Branch protection configured
- [ ] Collaborators added (if needed)
- [ ] Repository visibility set correctly
- [ ] Topics/tags added untuk discoverability

---

**Ready to push? Let's go! 🚀**
