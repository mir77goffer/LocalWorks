<?php
require_once __DIR__ . '/iniciar.php';

$titulo = $titulo ?? 'LocalWorks';
$paginaAtiva = $paginaAtiva ?? '';
$usuarioNav = usuarioLogado();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/estilo.css" rel="stylesheet">
</head>
<body>
    <a class="visually-hidden-focusable" href="#conteudo">Ir para o conteúdo</a>

    <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">
                <i class="bi bi-geo-alt-fill"></i> LocalWorks
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $paginaAtiva === 'inicio' ? 'active' : '' ?>" href="index.php">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $paginaAtiva === 'buscar' ? 'active' : '' ?>" href="buscar.php">Buscar profissionais</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $paginaAtiva === 'oportunidades' ? 'active' : '' ?>" href="oportunidades.php">Oportunidades</a>
                    </li>
                    <?php if ($usuarioNav && in_array($usuarioNav['tipo'], ['candidato', 'trabalhador'], true)): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $paginaAtiva === 'candidaturas' ? 'active' : '' ?>" href="minhas_candidaturas.php">Minhas candidaturas</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#como-funciona">Como funciona</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#para-quem">Para quem</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#ods8">ODS 8</a>
                    </li>
                </ul>

                <div class="d-flex gap-2 align-items-center">
                    <?php if ($usuarioNav): ?>
                        <a class="btn btn-outline-primary" href="painel.php">
                            <i class="bi bi-person-circle"></i>
                            <?= e(explode(' ', $usuarioNav['nome'])[0]) ?>
                        </a>
                        <form method="post" action="sair.php" class="m-0">
                            <?= csrfCampo() ?>
                            <button class="btn btn-primary" type="submit">Sair</button>
                        </form>
                    <?php else: ?>
                        <a class="btn btn-outline-primary" href="login.php">Entrar</a>
                        <a class="btn btn-primary" href="cadastro.php">Cadastre-se</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main id="conteudo">
