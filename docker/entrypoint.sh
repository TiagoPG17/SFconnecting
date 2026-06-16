#!/bin/sh
set -e

# Sync public files (baked in image) to shared volume so nginx can serve them
cp -rT /var/www/sfconnecting/public /mnt/sfconnecting-public

exec "$@"
