# Deploying Laravel Backend to Render (Free Tier)

This guide walks you through deploying the Finance Behavioral System backend to Render's free tier using Docker.

## Prerequisites

- [Render account](https://render.com) (free tier)
- GitHub repository with your Laravel application
- PostgreSQL database on Render (or another provider)

## Free Tier Limitations

> [!IMPORTANT]
> Render's free tier has the following constraints:
> - **Resources**: 0.1 CPU and 512 MB RAM
> - **Runtime**: 750 free hours per month (~31 days continuous operation)
> - **Instances**: Single instance only (no scaling)
> - **Restarts**: Services may restart at any time
> - **Storage**: No persistent disk storage
> - **Database**: Free PostgreSQL expires after 30 days

## Step 1: Create PostgreSQL Database

1. Log in to your [Render Dashboard](https://dashboard.render.com/)
2. Click **"New +"** → **"PostgreSQL"**
3. Configure your database:
   - **Name**: `finance-behavioral-db` (or your preferred name)
   - **Database**: `finance_behavioral`
   - **User**: Auto-generated
   - **Region**: Choose closest to your users
   - **Plan**: **Free**
4. Click **"Create Database"**
5. **Important**: Copy the **Internal Database URL** (format: `postgresql://user:password@host:port/database`)

> [!WARNING]
> Free PostgreSQL databases expire after 30 days. You'll need to create a new one and migrate your data.

## Step 2: Generate Application Key

Before deploying, generate a Laravel application key:

```bash
cd app/backend
php artisan key:generate --show
```

Copy the output (starts with `base64:`). You'll need this for the environment variables.

## Step 3: Create Web Service on Render

1. In the Render Dashboard, click **"New +"** → **"Web Service"**
2. Connect your GitHub repository:
   - Click **"Connect account"** if not already connected
   - Select your repository: `finance-behavioral-system`
3. Configure the service:
   - **Name**: `finance-behavioral-backend` (or your preferred name)
   - **Region**: Same as your database
   - **Branch**: `main` (or your production branch)
   - **Root Directory**: `app/backend`
   - **Environment**: **Docker**
   - **Plan**: **Free**

## Step 4: Configure Environment Variables

In the **"Advanced"** section, click **"Add Environment Variable"** and add the following:

### Required Variables

| Variable | Value | Notes |
|----------|-------|-------|
| `APP_NAME` | `Finance Behavioral System` | Your app name |
| `APP_ENV` | `production` | **Must be production** |
| `APP_KEY` | `base64:...` | From Step 2 |
| `APP_DEBUG` | `false` | **Must be false in production** |
| `APP_URL` | `https://your-app.onrender.com` | Update after deployment |
| `DATABASE_URL` | `postgresql://...` | From Step 1 (Internal Database URL) |
| `LOG_CHANNEL` | `stderr` | **Required for Render logging** |
| `LOG_LEVEL` | `error` | Recommended for production |

### Optional Variables

| Variable | Value | Notes |
|----------|-------|-------|
| `SESSION_DRIVER` | `database` | Default |
| `CACHE_STORE` | `database` | Default |
| `QUEUE_CONNECTION` | `database` | Default |

> [!TIP]
> You can add more environment variables later in the **"Environment"** tab of your service.

## Step 5: Deploy

1. Click **"Create Web Service"**
2. Render will:
   - Clone your repository
   - Build the Docker image (this takes 5-10 minutes on first deploy)
   - Run database migrations
   - Start your application
3. Monitor the deployment in the **"Logs"** tab

## Step 6: Update APP_URL

After deployment completes:

1. Copy your Render URL (e.g., `https://finance-behavioral-backend.onrender.com`)
2. Go to **"Environment"** tab
3. Update `APP_URL` with your actual Render URL
4. Click **"Save Changes"**
5. Render will automatically redeploy

## Step 7: Verify Deployment

Test your deployment:

```bash
# Health check
curl https://your-app.onrender.com/api/health

# Expected response:
# {"status":"ok","timestamp":"2026-01-22T12:48:54.000000Z"}
```

## Troubleshooting

### Build Fails

**Issue**: Docker build fails with "out of memory"
- **Solution**: Free tier has limited resources. The Dockerfile is optimized, but if it still fails, try:
  - Removing unused dependencies from `composer.json`
  - Using a smaller base image

**Issue**: "composer install" fails
- **Solution**: Check `composer.lock` is committed to your repository

### Application Errors

**Issue**: 500 Internal Server Error
- **Solution**: Check logs in Render Dashboard → **"Logs"** tab
- Common causes:
  - Missing `APP_KEY`
  - Database connection issues
  - Missing environment variables

**Issue**: Database connection refused
- **Solution**: 
  - Verify `DATABASE_URL` is correct (use **Internal** URL, not External)
  - Ensure database and web service are in the same region
  - Check database is running (free tier databases may sleep)

**Issue**: Mixed content warnings (HTTP/HTTPS)
- **Solution**: Already handled by `AppServiceProvider.php` forcing HTTPS

### Performance Issues

**Issue**: Slow response times
- **Solution**: Free tier has limited resources (0.1 CPU, 512 MB RAM)
  - Enable OPcache (already configured in `docker/php.ini`)
  - Use database caching (already configured)
  - Consider upgrading to paid tier for better performance

**Issue**: Service goes to sleep
- **Solution**: Free tier services may sleep after inactivity
  - First request after sleep takes longer
  - Consider using a cron job to ping your service every 10 minutes

## Updating Your Application

To deploy updates:

1. Push changes to your GitHub repository
2. Render automatically detects changes and redeploys
3. Monitor deployment in **"Logs"** tab

### Manual Deploy

To trigger a manual deploy:

1. Go to your service in Render Dashboard
2. Click **"Manual Deploy"** → **"Deploy latest commit"**

## Database Migrations

Migrations run automatically on each deployment via `docker/start.sh`.

To run migrations manually:

1. Go to **"Shell"** tab in Render Dashboard
2. Run:
   ```bash
   php artisan migrate --force
   ```

## Monitoring

### Logs

View logs in real-time:
- Render Dashboard → Your Service → **"Logs"** tab

### Metrics

Monitor your service:
- Render Dashboard → Your Service → **"Metrics"** tab
- Shows CPU, memory, and request metrics

## Cost Considerations

**Free Tier**:
- 750 hours/month (enough for 1 service running 24/7)
- Free PostgreSQL expires after 30 days
- No credit card required

**Paid Tier** (if you need to upgrade):
- Starter: $7/month (0.5 CPU, 512 MB RAM)
- Standard: $25/month (1 CPU, 2 GB RAM)
- PostgreSQL: $7/month (no expiration)

## Security Best Practices

> [!CAUTION]
> - Never commit `.env` file to Git
> - Always use environment variables for secrets
> - Keep `APP_DEBUG=false` in production
> - Use strong `APP_KEY` (generated by Laravel)
> - Regularly update dependencies

## Support

- [Render Documentation](https://docs.render.com/)
- [Laravel Documentation](https://laravel.com/docs)
- [Render Community Forum](https://community.render.com/)

## Next Steps

After successful deployment:

1. Set up custom domain (optional)
2. Configure CORS for frontend
3. Set up monitoring/alerts
4. Plan for database backup strategy
5. Consider upgrading to paid tier for production use
