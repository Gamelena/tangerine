#!/bin/bash
set -e

echo "Starting Tangerine Bootstrap..."

# 1. Install PHP Dependencies
echo "Running composer install..."
composer install

# 2. Install Frontend Dependencies
echo "Running bower install..."
bower install --allow-root

# 3. Create Symlinks (Legacy Aliases)
echo "Creating Symlinks..."
# Alias /libs "$TANGERINEPATH/public/js/libs"
# Alias /libs "$TANGERINEPATH/public/js/libs"
rm -rf public/libs
ln -s js/libs public/libs

# Alias /dojotoolkit is handled by .bowerrc (installs directly to public/dojotoolkit)
# No symlink needed.


# 4. Apply Patches
# echo "Applying Zend Test Patch..."
# bash tasks/zend-test-patch.sh

# 5. Create Log Directories (Runtime, to survive volume mount)
echo "Creating Log Directories..."
mkdir -p log cache
rm -rf log/debug # Ensure it's not a directory from previous runs
touch log/debug
chmod -R 777 log cache

# 6. Initialize Database (if requested/needed)
# We don't verify if it exists, just try to run it. 
# In a real run, this might fail if DB exists, but that's fine for now.
# echo "Initializing Database..."
# bash init_db.sh

echo "Bootstrap complete!"
