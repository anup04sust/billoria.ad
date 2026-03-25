#!/bin/bash
# Setup automatic hourly database backups for Billoria CMS API

PROJECT_DIR="/var/www/billoria.ad/cmsapi"
CRON_JOB="0 * * * * cd $PROJECT_DIR && /usr/local/bin/ddev backup-db-hourly >> /var/log/billoria-backup.log 2>&1"

echo "Setting up hourly database backups for Billoria CMS API..."

# Check if cron job already exists
if crontab -l 2>/dev/null | grep -q "billoria.*backup-db-hourly"; then
    echo "✓ Cron job already exists"
    echo "Current backup schedule:"
    crontab -l | grep billoria.*backup-db-hourly
else
    # Add cron job
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    echo "✓ Cron job added successfully"
    echo "Backup schedule: Every hour on the hour"
fi

# Create log file if it doesn't exist
sudo touch /var/log/billoria-backup.log 2>/dev/null || touch ~/billoria-backup.log
sudo chmod 666 /var/log/billoria-backup.log 2>/dev/null || chmod 666 ~/billoria-backup.log

echo ""
echo "Setup complete!"
echo ""
echo "Configuration:"
echo "  - Backups run: Every hour (e.g., 00:00, 01:00, 02:00...)"
echo "  - Retention: Last 24 backups (24 hours)"
echo "  - Location: $PROJECT_DIR/.ddev/db_snapshots/"
echo "  - Log file: /var/log/billoria-backup.log"
echo ""
echo "Manual commands:"
echo "  - Test backup now: cd $PROJECT_DIR && ddev backup-db-hourly"
echo "  - List backups: cd $PROJECT_DIR && ddev snapshot --list"
echo "  - Restore backup: cd $PROJECT_DIR && ddev snapshot restore <name>"
echo "  - View logs: tail -f /var/log/billoria-backup.log"
echo "  - Remove cron: crontab -e (then delete the billoria backup line)"
