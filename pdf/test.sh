batch=`ls -ltr | tail -5 | grep BATCHPDF`
file=`echo "$batch" | awk '{print $9}'`

echo "$file"


