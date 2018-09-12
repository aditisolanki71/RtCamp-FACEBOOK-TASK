<?php
    $out = $_GET['link'];
    header("Content-Type: plain/zip");
    header("Content-Disposition: Attachment; filename=".str_replace(" ","_",$out));
    header("Pragma: no-cache");
    readFile('zip/'.$out);
?>
