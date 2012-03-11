
echo "Clearing out the old rip"

rm -rf content/framework
mkdir content/framework

echo "about to call the ripping script"
echo "WARNING this script assumes you have a database installed"

php -e -f scripts/rip.php

echo "script complete"

