#!/bin/bash

# Script to upgrade PHP to 8.3 for Tic-Tac-Toe project
# Run with: sudo bash upgrade-php.sh

set -e

echo "Adding ondrej/php repository..."
add-apt-repository ppa:ondrej/php -y

echo "Updating package list..."
apt update

echo "Installing PHP 8.3 with required extensions..."
apt install -y php8.3 php8.3-cli php8.3-curl php8.3-mbstring php8.3-xml php8.3-sqlite3 php8.3-zip php8.3-bcmath php8.3-intl

echo "Setting PHP 8.3 as default..."
update-alternatives --set php /usr/bin/php8.3

echo ""
echo "PHP upgrade complete! Verifying installation..."
php -v

echo ""
echo "Checking PHP extensions..."
php -m | grep -E "(curl|mbstring|xml|sqlite3)"

echo ""
echo "✓ PHP 8.3 is now set as default"

