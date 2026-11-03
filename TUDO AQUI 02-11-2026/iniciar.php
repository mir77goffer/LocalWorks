<?php
declare(strict_types=1);

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

date_default_timezone_set('America/Sao_Paulo');

if (session_status() === PHP_SESSION_NONE) {
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => $https,
    ]);

    session_start();
}

require_once __DIR__ . '/../src/Conexao.php';
require_once __DIR__ . '/../src/Autenticacao.php';
require_once __DIR__ . '/../src/Profissionais.php';
require_once __DIR__ . '/../src/Painel.php';
require_once __DIR__ . '/../src/Oportunidades.php';

function e(mixed $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function formatarValor(mixed $valor): string
{
    if ($valor === null || $valor === '') {
        return 'A combinar';
    }

    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

function tempoDecorrido(?string $data): string
{
    if (!$data) {
        return '';
    }

    try {
        $agora = new DateTimeImmutable('now');
        $inicio = new DateTimeImmutable($data);
    } catch (Throwable $ex) {
        return '';
    }

    $seg = $agora->getTimestamp() - $inicio->getTimestamp();

    if ($seg < 60) {
        return 'agora há pouco';
    }

    if ($seg < 3600) {
        return 'há ' . intdiv($seg, 60) . ' min';
    }

    if ($seg < 86400) {
        return 'há ' . intdiv($seg, 3600) . ' h';
    }

    $dias = intdiv($seg, 86400);

    if ($dias < 30) {
        return 'há ' . $dias . ($dias === 1 ? ' dia' : ' dias');
    }

    return 'em ' . $inicio->format('d/m/Y');
}

function usuarioLogado(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function logar(array $usuario): void
{
    session_regenerate_id(true);
    $_SESSION['usuario'] = $usuario;
}

function sair(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'] ?? '',
            (bool) $params['secure'],
            (bool) $params['httponly']
        );
    }

    session_destroy();
}

function exigirLogin(): void
{
    if (!usuarioLogado()) {
        header('Location: login.php');
        exit;
    }
}

function csrfCampo(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function csrfValido(mixed $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $token);
}
