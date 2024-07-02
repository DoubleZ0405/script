#!/bin/bash
START=`date +%s%N`;
for i in `seq 0 9`
do

    nohup java -jar ft_local/reporter-1.0-SNAPSHOT-jar-with-dependencies.jar "2021-02-04 07:52:23" "2021-02-04 12:55:23" 2>&1 > ret.log &
    php/bin/php app/video/script/videoEdit/recallVideoSharpness.php $i $1 &
done
wait
END=`date +%s%N`;
time=$((END-START))
time=`expr $time / 1000000`
echo $time ms
echo "----- end -----"