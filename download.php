<?php
$out = $_GET['link'];
header("Content-Type: plain/zip");
header("Content-Disposition: Attachment; filename=".$out);
header("Pragma: no-cache");
echo "$out";
?>
