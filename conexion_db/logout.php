<?php
session_start();
session_unset();
session_destroy();
header("Location: ../principal_views/login.php");
exit;
