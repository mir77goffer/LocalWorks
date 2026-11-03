<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/iniciar.php';

if (usuarioLogado()) {
    header('Location: painel.php');
    exit;
}

$erro = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');

    if (!csrfValido($_POST['csrf'] ?? '')) {
        $erro = 'Sessão expirada. Envie o formulário novamente.';
    } else {
        try {
            $usuario = Autenticacao::entrar($email, $senha);

            if ($usuario) {
                logar($usuario);
                header('Location: painel.php');
                exit;
            }

            $erro = 'E-mail ou senha incorretos.';
        } catch (Throwable $ex) {
            error_log('login.php: ' . $ex->getMessage());
            $erro = 'Não foi possível entrar agora. Tente novamente.';
        }
    }
}

$titulo = 'Entrar – LocalWorks';
require_once __DIR__ . '/../includes/cabecalho.php';
?>

<section class="secao">
    <div class="container" style="max-width: 28rem;">
        <h1 class="h2 fw-bold mb-4">Entrar</h1>

        <?php if (($_GET['cadastro'] ?? '') === 'ok'): ?>
            <div class="alert alert-success">Cadastro realizado! Entre com seu e-mail e senha.</div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="post" class="cartao p-4">
            <?= csrfCampo() ?>

            <div class="mb-3">
                <label class="form-label" for="email">E-mail</label>
                <input class="form-control" type="email" id="email" name="email" value="<?= e($email) ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label" for="senha">Senha</label>
                <input class="form-control" type="password" id="senha" name="senha" required>
            </div>

            <button class="btn btn-primary w-100" type="submit">Entrar</button>
        </form>

        <p class="text-center mt-3">
            Ainda não tem conta?
            <a href="cadastro.php">Cadastre-se</a>
        </p>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
