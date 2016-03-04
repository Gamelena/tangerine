ROOT_DIR=`readlink -f $PWD/..`
cd ../vendor/zendframework/zendframework1/library/Zend/Test/PHPUnit/
echo "Generando parche para Zend_Test_PHPUnit_ControllerTestCase"
echo ""
diff -u ControllerTestCase.php $ROOT_DIR/forks/Zend/Test/PHPUnit/ControllerTestCase.php > phpunit4.patch
patch < phpunit4.patch
cd -


