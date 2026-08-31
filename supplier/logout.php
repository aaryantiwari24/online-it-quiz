<?php
session_start();
session_unset();
session_destroy();
header("Location: ../authentication/supplier_login.php");
exit();
?>