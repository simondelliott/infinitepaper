
echo "Executing all the unit tests"

for f in ./lib/framework/tests/unit/*Test.php;
do
    echo "[EXECUTE Test]" $f;
    phpunit --bootstrap ./lib/framework/cli_common.php $f;
done


echo "Done"

