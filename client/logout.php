<?php
require_once '../config/bootstrap.php';

unset($_SESSION['customer_id'], $_SESSION['customer_name']);
App::redirect('../index.php');
?>
