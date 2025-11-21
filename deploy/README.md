# 🚀 Deployment Scripts & Guides

Folder ini berisi semua file yang dibutuhkan untuk deployment SIPAKRT ke berbagai environment.

## 📁 File Structure

### Deployment Scripts
- **`deploy-from-github-cpanel.sh`** - Deploy ke cPanel via GitHub (standard)
- **`deploy-from-github-cpanel-lowmem.sh`** - Deploy ke cPanel dengan low memory (ZIP method)
- **`deploy-from-github.sh`** - Deploy umum dari GitHub
- **`deploy-cpanel.sh`** - Deploy manual ke cPanel
- **`fix-deploy.sh`** - Quick fix untuk deployment issues

### Documentation
- **`DEPLOYMENT-FROM-GITHUB.md`** - Panduan lengkap deployment dari GitHub (recommended)
- **`DEPLOYMENT.md`** - Panduan deployment production (Nginx/Apache)
- **`GITHUB_DEPLOYMENT.md`** - GitHub deployment workflow
- **`QUICK-DEPLOY-GUIDE.txt`** - Quick reference untuk deployment
- **`QUICK-FIX-CLONE-ERROR.txt`** - Troubleshooting git clone error
- **`TROUBLESHOOTING-CLONE-ERROR.txt`** - Detailed troubleshooting guide

### Server Configuration
- **`nginx.conf`** - Nginx configuration untuk production
- **`supervisord.conf`** - Supervisor configuration untuk background processes

---

## 🎯 Quick Start

### Deploy ke cPanel (Recommended)

```bash
# Download deployment script
wget https://raw.githubusercontent.com/fajarnrs/SIPAKRT/main/deploy/deploy-from-github-cpanel-lowmem.sh
chmod +x deploy-from-github-cpanel-lowmem.sh

# Run deployment
./deploy-from-github-cpanel-lowmem.sh
```

### Update File Tertentu

```bash
# Update single file dari GitHub
wget https://raw.githubusercontent.com/fajarnrs/SIPAKRT/main/path/to/file.php -O path/to/file.php
```

---

## 📚 Documentation

Untuk panduan lengkap, baca:
1. [DEPLOYMENT-FROM-GITHUB.md](DEPLOYMENT-FROM-GITHUB.md) - Main deployment guide
2. [DEPLOYMENT.md](DEPLOYMENT.md) - Production setup guide

---

**Built with ❤️ by [fajarnrs](https://github.com/fajarnrs)**
