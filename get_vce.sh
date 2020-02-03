#!/bin/bash

#set -x

# Download list of .vce files
WORK_DIR=/home/vlad/Downloads/vce
LOG_DIR=$WORK_DIR/log
TEMP_DIR=$WORK_DIR/temp

echo Download list of files "`date +%Y-%m-%d_%H:%M:%S` --------------------------------------------------------------"
cd $TEMP_DIR
rm -f links.txt full_links.txt new_files.txt
touch new_files.txt
proxychains wget -U "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1" -o $LOG_DIR/wget_links.log -A vce,VCE -m -p -E -k -K -nc -np http://examsforall.com/exams

# Save links to file
echo Save links to file "`date +%Y-%m-%d_%H:%M:%S` ------------------------------------------------------------------"
cd $WORK_DIR/examsforall.com/exams/download
ls > $TEMP_DIR/links.txt
cd $TEMP_DIR
awk '{print "http://examsforall.com/exams/download/"$0"/"}' links.txt > full_links.txt

# Split links file to files with 10 links
echo Split links to files "`date +%Y-%m-%d_%H:%M:%S` -----------------------------------------------------------------"
cd $TEMP_DIR/links/
rm -f x*
split -dl 10 ../full_links.txt

# Download vce files. Use new proxy after 10 files download
echo Start downloads "`date +%Y-%m-%d_%H:%M:%S` -----------------------------------------------------------------------"
cd $TEMP_DIR/files/
LINKSL=$TEMP_DIR/links/x*
for f in $LINKSL
do
	echo Downloading from "$f `date +%Y-%m-%d_%H:%M:%S` --"
	service tor restart
	#Use new proxy after 10 files download
	proxychains wget -o $LOG_DIR/wget_files.log -nc --content-disposition -i $f
done

find . -type f -mtime -1 -name "*.vce" > $TEMP_DIR/new_files.txt
mail -s "NEW .vce files" ********@gmail.com < $TEMP_DIR/new_files.txt

find . -type f -mtime -7 -name "*.vce" -exec cp {} /var/www/html/vce-exams.eu/files5874v/vce \;

cd $WORK_DIR/examsforall.com/exams
find . -type d -name "sort-by*" -exec rmdir {} \;
find . -type d -name "*q.vce" -exec rmdir {} \;


echo ------------------------------------------------------------------------------------------------------
echo JOB DONE "`date +%Y-%m-%d_%H:%M:%S` ------------------------------------------------------------------"
echo ------------------------------------------------------------------------------------------------------

