<?php

function RandomString()
{
    $length = mt_rand(150,250);
    return str_shuffle(substr(str_repeat(md5(mt_rand()), 2+$length/32), 0, $length))."\n";
}

$fp = fopen('./data/a_100.txt', 'a+');
$fp2 = fopen('./data/b_100.txt', 'a+');

for($i=0; $i<100000; $i++) {
    $rs = RandomString();
    fwrite($fp, $rs);
    if(mt_rand(0,100)==13) fwrite($fp2, $rs); else fwrite($fp2, RandomString());
}
fclose($fp);
fclose($fp2);
