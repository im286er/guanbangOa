<?php



$uploaddir = '../uploads/';

$uploadfile = $uploaddir. $_FILES['file']['name'];

print "<pre>";
if (move_uploaded_file($_FILES['file']['tmp_name'], $uploaddir.$_FILES['file']['name'])) {
   # print "File is valid, and was successfully uploaded.  Here's some more debugging info:\n";
  #echo $_SERVER['PHP_SELF'];
  echo $_SERVER["QUERY_STRING"];
   print_r($_FILES);
   #print $_FILES['file']['name'];
} else {
    print "Possible file upload attack!  Here's some debugging info:\n";
    print_r($_FILES);
}
print "</pre>";
 