<!DOCTYPE html>
<html>
<body>

<?php
error_reporting(E_ALL);

ini_set(‘display_errors’, TRUE);

ini_set(‘display_startup_errors’, TRUE);

function familyName($fname, $year) {
    echo "$fname Refsnes. Born in $year <br>";
}

familyName("Hege","1975");
familyName("Stale","1978");
familyName("Kai Jim","1983");
?>

</body>
</html>