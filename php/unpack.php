<?php
$str = "\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d";
print substr($str, 0, 4);
print_r(unpack("L*",substr($str,1,4)));
print_r(unpack("H*",$str));
print_r(unpack("h*",$str));
print "-----------\n";
//print_r( sscanf(strrev(substr($str,0,4)), "%x"));
print sprintf("%d", 0x01020304);
print "-----------\n";
print sprintf("%d", 0x04030201);
print "-----------\n";
print_r( unpack("I*",substr($str,0,4)));
list(,$num) = unpack("I*",substr($str,0,4));
print "num=$num\n";
print_r( unpack("I*",strrev(substr($str,0,4))));
print "-----------\n";
print_r(unpack("H*",strrev(substr($str,0,4))));
print_r(unpack("N*",substr($str,0,4)));
print_r(unpack("V*",substr($str,0,4)));
?>
