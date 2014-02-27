<?php

$output = null;
exec('tail -n 1 data.logson',$output);

list($idx,$milli,$times) = json_decode($output[0]);

$last = filemtime('data.logson');
$start = $last-$milli/1000;

echo "\nLast Modification: ". date('r',$last);
echo "\nStart: ". date('r',$start);

file_put_contents('start', $start);