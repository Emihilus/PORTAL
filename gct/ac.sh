#!/bin/bash
start_time=$(date +%s)
line=$(head -n 1 acs.set)
v=-1
while [ $line == "1" ] ;
do
end_time=$(date +%s)
elapsed=$(( end_time - start_time ))
# echo "$elapsed"
if [ "$elapsed" -gt "$v" ] ; then
git add -A
git commit -m "AUTOCOMMIT: $(date +%d-%m-%Y_%H:%M:%S)"
git push -u origin master
start_time=$(date +%s)
v=600
fi
sleep 10
line=$(head -n 1 acs.set)
done
echo "TERMINATED"