
echo "Executing all the unit tests"

for f in ./tests/unit/*Test.php;
do
    phpunit --bootstrap ./lib/framework/cli_common.php $f;
done


echo "Done"

