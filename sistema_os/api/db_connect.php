<?php
// api/db_connect.php

$servername = "localhost";
$username = "root"; // Padrão do XAMPP
$password = "";     // Padrão do XAMPP
$dbname = "gestao_os";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Checar conexão
if ($conn->connect_error) {
  // Em produção, não exiba o erro diretamente. Apenas registre em um log.
  die("Connection failed: " . $conn->connect_error);
}

// Define o charset para UTF-8 para evitar problemas com acentuação
$conn->set_charset("utf8mb4");
?>
