#!/usr/bin/env bash

START=`date + %S%N`
#time2=$(date "+%Y%m%d%H%M")
/usr/local/bin/php getNe4South1.php
echo "开始随机等待 1-10 秒..."
# 1-10秒内随机
sleep 5
echo "等待后继续"

/usr/local/bin/php getNe4South2.php
echo "开始随机等待 1-10 秒..."
# 1-10秒内随机
sleep 5
echo "等待后继续, 华南2 success"


/usr/local/bin/php computeOpTreeNeInfoNoth1.php
echo "开始随机等待 1-10 秒..."
# 1-10秒内随机
sleep 5
echo "等待后继续, 华北1 success"

/usr/local/bin/php computeOpTreeNeInfoNoth2.php
echo "开始随机等待 1-10 秒..."
# 1-10秒内随机
sleep 5
echo "等待后继续, 华北2 success"

#
/usr/local/bin/php getNeForEast1.php
echo "开始随机等待 1-10 秒..."
# 1-10秒内随机
sleep 5
echo "等待后继续, 华东1 success"

/usr/local/bin/php getNeForEast2.php
echo "开始随机等待 1-10 秒..."
# 1-10秒内随机
sleep 5
echo "等待后继续, All success"

#wait
END=`date +%s%N`;
time=$((END-START))
time=`expr $time / 1000000`
echo $time ms
echo "SUCCESS"
echo "起始"+$START+"终止"+$END
