<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/iniciar.php';

$erro = '';
$ok = '';
$bloqueado = true;

try {
    $bloqueado = Painel::existeAdmin();
} catch (Throwable $ex) {
    $erro = 'Execute o arquivo database/LocalWorks.sql antes de usar esta página.';
}

if (!$bloqueado && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');

    if (!csrfValido($_POST['csrf'] ?? '')) {
        $erro = 'Sessão expirada. Tente novamente.';
    } elseif ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 8) {
        $erro = 'Informe nome, e-mail válido e senha com pelo menos 8 caracteres.';
    } else {
        try {
            Painel::criarAdmin($nome, $email, password_hash($senha, PASSWORD_DEFAULT));
            $ok = 'Administrador criado! Apague este arquivo e entre pelo login.';
            $bloqueado = true;
        } catch (Throwable $ex) {
            $erro = $ex->getCode() === '23000'
                ? 'Este e-mail já está cadastrado.'
                : 'Não foi possível criar o administrador.';
        }
    }
}

$titulo = 'Criar administrador – LocalWorks';
require_once __DIR__ . '/../includes/cabecalho.php';
?>

<section class="secao">
    <div class="container" style="max-width: 28rem;">
        <h1 class="h2 fw-bold mb-4">Criar administrador</h1>

        <?php if ($ok): ?>
            <div class="alert alert-success"><?= e($ok) ?></div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= e($erro) ?></div>
        <?php endif; ?>

        <?php if ($bloqueado): ?>
            <?php if (!$ok && !$erro): ?>
                <div class="alert alert-warning">Já existe um administrador. Apague este arquivo.</div>
            <?php endif; ?>
        <?php else: ?>
            <form method="post" class="cartao p-4">
                <?= csrfCampo() ?>

                <div class="mb-3">
                    <label class="form-label" for="nome">Nome</label>
                    <input class="form-control" id="nome" name="nome" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="email">E-mail</label>
                    <input class="form-control" type="email" id="email" name="email" required>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="senha">Senha</label>
                    <input class="form-control" type="password" id="senha" name="senha" minlength="8" required>
                </div>

                <button class="btn btn-primary w-100" type="submit">Criar administrador</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
