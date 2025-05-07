<?php
mysqli_report(MYSQLI_REPORT_ERROR);
$servidor = "localhost";
// Intentar con el usuario actual del sistema
$usuario = exec('whoami');
$clave = "";

@$mysqli = new mysqli($servidor, $usuario, $clave);
if ($mysqli->connect_errno) {
  echo "Fallo conexión a Mysql: " . $mysqli->connect_errno . " " . $mysqli->connect_error;
} else {
  $basedatos = "gatos";
  if (!$mysqli->select_db($basedatos)) {
    // Si la base de datos no existe, intentar crearla
    $sql = "CREATE DATABASE IF NOT EXISTS gatos";
    $mysqli->query($sql);
    $mysqli->select_db($basedatos);
  }
}
