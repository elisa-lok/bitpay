#!/bin/bash
a=`ps -ef|grep qrcode|grep -v grep|wc -l`
basepath=$(dirname $(readlink -f $0))
if [[ $a == 0 ]]
then
  $basepath/qrcode -port=8080 >> ./qrcode.log
fi
