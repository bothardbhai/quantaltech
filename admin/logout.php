<?php
$skip_auth = true;
require __DIR__ . '/bootstrap.php';
auth_logout();
header('Location: ' . ADMIN_URL . '/login.php');
exit;
