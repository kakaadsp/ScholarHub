<?php
require_once 'conf.php';
session_destroy();
header('Location: login.php');
exit;
