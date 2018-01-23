<?php

G::LoadClass("case");

/*
$url = "http://localhost:8069";
$db = "odoo";
$username = "dani";
$password = "postgres";*/

echo G::json_encode( $_POST);
$APP_UID = $_SESSION['APPLICATION'];

try {
 
} catch (Exception $e) {
  echo null;
}
?>