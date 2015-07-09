set -o nounset                              # Treat unset variables as an error
#!/bin/bash

test="Welcome to the Land of Linux"

echo "Our variable test is ${#test} characters long"

test1=${test:0:7}
test2=${test:15:13}
test3=${test:0}

echo $test1
echo $test2
echo $test3

test="land.of.linux"
echo "Stripping the shortest match from front:"
echo ${test#*.}

echo "Stripping the shortest match from back:"
echo ${test%.*}

echo "Stripping the longest match from front:" 
echo ${test##*.}

echo "Stripping the longest match from back:" 
echo ${test%%.*}

test="one two three one four one five"

echo "Before replacement: $test"

echo "After replacement: ${test//one/xxx}"


test="AaBbCcDdEeFfGg"

testa=`expr index "$test" C`
testb=`expr index "$test" D`
testc=`expr index "$test" E`
testd=`expr index "$test" Z`

echo "testa is in position: $testa"
echo "testb is in position: $testb"
echo "testc is in position: $testc"
echo "testd is in position: $testd"
