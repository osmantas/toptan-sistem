<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['action'] = 'teslimat_bekleyen';
require 'api/siparis_api.php';
echo "\nDONE\n";
