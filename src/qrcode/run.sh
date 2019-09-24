#!/bin/bash
a=`ps -ef|grep qrcode|grep -v grep|wc -l`
if [[ $a == 0 ]]
then
     ./qrcode -port=8080 >> ./qrcode.log
fi
