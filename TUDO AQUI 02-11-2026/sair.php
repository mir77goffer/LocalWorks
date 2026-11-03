<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/iniciar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrfValido($_POST['csrf'] ?? '')) {
    sair();
}

header('Location: index.php');
exit;
