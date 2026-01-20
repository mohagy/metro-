# Deployment Guide for GitHub Pages

## Overview

This guide explains how to deploy the Flutter web application to GitHub Pages.

## Important Notes

⚠️ **GitHub Pages Limitations:**
- GitHub Pages only hosts **static files** (HTML, CSS, JavaScript)
- **PHP backend will NOT work** on GitHub Pages
- You'll need to host the PHP backend separately (see options below)

## Deployment Steps

### 1. Build Flutter Web App

```bash
flutter build web --release --base-href "/metro/"
```

The `--base-href` flag sets the base path for your app on GitHub Pages.

### 2. GitHub Repository Setup

1. Create a new repository on GitHub (or use existing)
2. Initialize git (if not already done):
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git branch -M main
   git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git
   git push -u origin main
   ```

### 3. Enable GitHub Pages

1. Go to your repository on GitHub
2. Click **Settings** → **Pages**
3. Under **Source**, select:
   - **Branch**: `gh-pages` (or `main` if using root)
   - **Folder**: `/ (root)` or `/docs` if you want to use docs folder
4. Click **Save**

### 4. Automatic Deployment (Recommended)

The repository includes a GitHub Actions workflow (`.github/workflows/deploy.yml`) that will:
- Automatically build the Flutter app when you push to `main`
- Deploy to GitHub Pages automatically

**To use automatic deployment:**
1. The workflow is already configured
2. Just push to the `main` branch
3. GitHub Actions will build and deploy automatically

### 5. Manual Deployment

If you prefer manual deployment:

```bash
# Build the app
flutter build web --release --base-href "/metro/"

# Create gh-pages branch (if it doesn't exist)
git checkout --orphan gh-pages
git rm -rf .

# Copy build files
cp -r build/web/* .

# Commit and push
git add .
git commit -m "Deploy to GitHub Pages"
git push origin gh-pages

# Switch back to main branch
git checkout main
```

## Backend Options

Since GitHub Pages can't run PHP, you have several options:

### Option 1: Host PHP Backend Separately

**Free Options:**
- **000webhost** - Free PHP hosting
- **InfinityFree** - Free PHP hosting
- **Heroku** - Free tier (limited)
- **Railway** - Free tier available
- **Render** - Free tier available

**Paid Options:**
- **DigitalOcean** - $5/month
- **Linode** - $5/month
- **AWS EC2** - Pay as you go
- **Google Cloud Platform** - Free tier available

### Option 2: Use Firebase Functions

Convert your PHP backend to Firebase Cloud Functions:
- Serverless
- Free tier available
- Integrates with Firebase

### Option 3: Use Your Own Server

If you have your own server:
1. Deploy PHP backend to your server
2. Update `api_config.dart` with your server URL
3. Ensure CORS is configured correctly

## Configuration Updates

### Update API Config for Production

1. Update `lib/config/api_config.dart`:
   ```dart
   static String get baseUrl {
     if (kIsWeb) {
       // Use your production backend URL
       return 'https://your-backend-domain.com';
     }
     return 'http://localhost';
   }
   ```

2. Or create environment-based config:
   - Development: `http://localhost`
   - Production: `https://your-backend-domain.com`

### CORS Configuration

Make sure your PHP backend has CORS headers configured in `backend/api/config.php`:

```php
header('Access-Control-Allow-Origin: https://your-username.github.io');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

## GitHub Pages URL

After deployment, your app will be available at:
```
https://YOUR_USERNAME.github.io/metro/
```

Or if you configured a custom domain:
```
https://your-custom-domain.com
```

## Troubleshooting

### App not loading
- Check the base-href matches your repository name
- Verify all assets are in the build/web folder
- Check browser console for errors

### API calls failing
- Verify backend URL is correct
- Check CORS configuration
- Ensure backend is accessible from the internet

### Build fails
- Check Flutter version compatibility
- Verify all dependencies are available
- Check GitHub Actions logs for errors

## Next Steps

1. **Deploy backend** to a PHP hosting service
2. **Update API config** with production backend URL
3. **Test the deployment** on GitHub Pages
4. **Configure custom domain** (optional)

