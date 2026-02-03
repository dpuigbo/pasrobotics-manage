#!/bin/bash
# Complete deployment and troubleshooting script
# Run this on your server

# Set your project path here
PROJECT_PATH="$HOME/public_html"  # Change this to your actual path

cd "$PROJECT_PATH"

echo "=== Step 1: Uploading/pulling latest code ==="
# If using git:
git pull origin main
# Otherwise, manually upload the files before running this script

echo ""
echo "=== Step 2: Regenerating Composer autoloader ==="
composer dump-autoload

echo ""
echo "=== Step 3: Clearing all Laravel caches ==="
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan filament:clear-cached-components

echo ""
echo "=== Step 4: Rebuilding optimized files ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "=== Step 5: Checking if model files exist and have correct content ==="
echo "Checking ControllerModel..."
grep -n "protected \$table = 'component_models';" app/Models/ControllerModel.php

echo "Checking MechanicalUnitModel..."
grep -n "protected \$table = 'component_models';" app/Models/MechanicalUnitModel.php

echo "Checking DriveUnitModel..."
grep -n "protected \$table = 'component_models';" app/Models/DriveUnitModel.php

echo ""
echo "=== Step 6: Checking if RelationManager files exist ==="
ls -la app/Filament/Resources/ControllerModelResource/RelationManagers/TemplateVersionsRelationManager.php
ls -la app/Filament/Resources/RobotModelResource/RelationManagers/TemplateVersionsRelationManager.php
ls -la app/Filament/Resources/DriveUnitModelResource/RelationManagers/TemplateVersionsRelationManager.php

echo ""
echo "=== Deployment complete! ==="
echo "Now try accessing: https://manage.pasrobotics.com/admin/controller-models"
