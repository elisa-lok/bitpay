#!/bin/bash
a=`ps -ef|grep support|grep cli.php|grep feedback|wc -l`
path='/XXXXXX/cli/cli.php'
if [[ $a == 0 ]]
then
     nohup /data/php/bin/php $path feedback &
fi

b=`ps -ef|grep support|grep cli.php|grep push|wc -l`
if [[ $b == 0 ]]
then
     nohup /data/php/bin/php $path push &
fi
