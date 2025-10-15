<?php
// Define um título padrão se a variável $page_title não for definida na página que o inclui.
$page_title = $page_title ?? 'Sistema de Gestão de O.S.';

// Define a URL base para os links. Útil se o projeto estiver em um subdiretório.
$base_url = '/Projeto%20Novo/sistema_os/';
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base_url ?>style.css">
    <?php if (isset($extra_css)) { echo $extra_css; } // Permite adicionar CSS extra específico da página ?>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-dark navbar-dark border-bottom border-body" data-bs-theme="dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= $base_url ?>dashboard.php">
      <i class="bi bi-tools text-warning"></i>
      Gestão O.S.
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-navbar" aria-controls="main-navbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="main-navbar">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
      <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>listar_solicitacoes.php"><i class="bi bi-bell"></i> Solicitações</a></li>  
      <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>dashboard.php"><i class="bi bi-speedometer2"></i> Início</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>index.php"><i class="bi bi-plus-circle"></i> Nova O.S.</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>ordens_servico.php"><i class="bi bi-list-task"></i> Lista de Ordens</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>equipamentos.php"><i class="bi bi-wrench-adjustable-circle"></i> Equipamentos</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>listar_planos.php"><i class="bi bi-calendar-week"></i> Planos de Manutenção</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Bootstrap e nosso script principal -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base_url ?>script.js"></script>