# Troubleshooting Blank Page on GitHub Pages

## ✅ Fixed: Base-href Mismatch

The base-href has been updated from `/metro/` to `/metro-/` to match your repository name.

## Next Steps

### 1. Wait for GitHub Actions to Rebuild

The fix has been pushed. GitHub Actions will automatically:
- ✅ Rebuild your app with the correct base-href
- ✅ Deploy to GitHub Pages

**Check the deployment:**
- Go to: https://github.com/mohagy/metro-/actions
- Look for the latest workflow run
- Wait for it to complete (5-10 minutes)

### 2. Verify GitHub Pages is Enabled

1. Go to: https://github.com/mohagy/metro-/settings/pages
2. Make sure **Source** is set to:
   - **GitHub Actions** (recommended)
   - OR **Deploy from a branch** → `main` → `/ (root)`

### 3. Clear Browser Cache

After deployment completes:
- Hard refresh: `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)
- Or open in incognito/private window

### 4. Check Browser Console

If still blank, open browser console (F12) and check for errors:
- **404 errors**: Base-href issue (should be fixed now)
- **CORS errors**: Backend API issues (expected - PHP needs separate hosting)
- **Firebase errors**: Check Firebase configuration

## Common Issues

### Issue: "404 Not Found" for assets
**Solution**: Base-href mismatch (FIXED - wait for rebuild)

### Issue: Blank white page
**Possible causes:**
1. JavaScript errors - Check browser console
2. Firebase initialization failed - Check Firebase config
3. Build not complete - Wait for GitHub Actions

### Issue: "Failed to load resource"
**Solution**: This is normal - PHP backend needs separate hosting

## Verify Deployment

After the workflow completes, check:
1. **Actions tab**: https://github.com/mohagy/metro-/actions
   - Should show green checkmark ✅
2. **Pages tab**: https://github.com/mohagy/metro-/settings/pages
   - Should show "Your site is live at https://mohagy.github.io/metro-/"

## Expected Behavior

✅ **Working correctly:**
- App loads and shows login/register screen
- Firebase authentication works
- UI displays properly

⚠️ **Expected to fail (until backend is hosted):**
- File uploads (needs PHP backend)
- API calls to PHP endpoints

## Still Blank After Rebuild?

1. **Check Actions logs**: Look for build errors
2. **Check browser console**: Look for JavaScript errors
3. **Verify base-href**: Should be `/metro-/` in built files
4. **Try different browser**: Rule out browser-specific issues

## Quick Test

After rebuild completes, try:
```
https://mohagy.github.io/metro-/
```

If still blank, check:
- Browser console (F12) for errors
- Network tab for failed requests
- GitHub Actions logs for build errors

