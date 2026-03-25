# Automatic Database Backup System

This directory contains the automatic hourly database backup system for the Billoria CMS API.

## Setup

The backup system is configured to automatically create database snapshots every hour when DDEV is running.

### Installation
Run the setup script to enable automatic backups:
```bash
cd /var/www/billoria.ad/cmsapi
./.ddev/setup-hourly-backup.sh
```

## Configuration

- **Schedule**: Every hour on the hour (00:00, 01:00, 02:00, etc.)
- **Retention**: Last 24 backups (rolling 24-hour window)
- **Storage Location**: `.ddev/db_snapshots/`
- **Naming**: `auto_hourly_YYYYMMDD_HHMM`
- **Log File**: `/var/log/billoria-backup.log`

## How It Works

1. **Cron Job**: A crontab entry runs every hour
2. **Status Check**: Script checks if DDEV is running
3. **Create Snapshot**: If running, creates a timestamped snapshot
4. **Cleanup**: Automatically removes backups older than 24 hours

## Manual Usage

### Create a backup now
```bash
ddev backup-db-hourly
```

### List all backups
```bash
ddev snapshot --list
```

### Restore a specific backup
```bash
ddev snapshot restore auto_hourly_20260326_0348
```

### View backup logs
```bash
tail -f /var/log/billoria-backup.log
```

### Create a manual named backup
```bash
ddev snapshot --name="before-migration"
```

## Troubleshooting

### Check if cron job is running
```bash
crontab -l | grep billoria
```

### Test the backup command
```bash
cd /var/www/billoria.ad/cmsapi
ddev backup-db-hourly
```

### Check backup location
```bash
ls -lah .ddev/db_snapshots/
```

### Remove automatic backups
```bash
crontab -e
# Delete the line containing "billoria-backup"
```

## Important Notes

- Backups only run when DDEV is running
- Backups are stored locally in `.ddev/db_snapshots/`
- Old backups (> 24 hours) are automatically cleaned up
- Manual backups (without `auto_hourly_` prefix) are not auto-deleted
- For production, consider additional off-site backup solutions

## File Structure

```
.ddev/
├── commands/
│   └── host/
│       └── backup-db-hourly       # Backup script (DDEV command)
├── setup-hourly-backup.sh         # Setup script for cron
└── db_snapshots/                  # Backup storage directory
    └── auto_hourly_YYYYMMDD_HHMM/ # Individual backups
```
