<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/iniciar.php';

exigirLogin();

$u = usuarioLogado();

if ($u['tipo'] === 'administrador') {
    header('Location: painel.php');
    exit;
}

$erro = '';
$nome = '';
$descricao = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $descricao = trim((string) ($_POST['descricao'] ?? ''));

    if (!csrfValido($_POST['csrf'] ?? '')) {
        $erro = 'Sessão expirada. Atualize a página e tente novamente.';
    } elseif ($nome === '' || mb_strlen($nome) > 100) {
        $erro = 'Informe o nome da categoria (até 100 caracteres).';
    } elseif (mb_strlen($descricao) > 500) {
        $erro = 'A descrição pode ter no máximo 500 caracteres.';
    } else {
        try {
            if (Painel::sugerirCategoria($nome, $descricao)) {
                $_SESSION['flash'] = 'Sugestão enviada! O administrador vai analisar a categoria.';
                header('Location: painel.php');
                exit;
            }

            $erro = 'Essa categoria já existe ou já foi sugerida.';
        } catch (Throwable $ex) {
            error_log('sugerir_categoria.php: ' . $ex->getMessage());
            $erro = 'Não foi possível enviar a sugestão. Tente novamente.';
        }
    }
}

$titulo = 'Sugerir categoria – LocalWorks';
require_once __DIR__ . '/../includes/cabecalho.php';
?>

<section class="secao">
    <div class="container" style="max-width: 36rem;">
        <a href="painel.php" class="d-inline-block mb-3">
            <i class="bi bi-arrow-left"></i> Voltar ao painel
        </a>

        <h1 class="h2 fw-bold mb-2">Sugerir nova categoria</h1>

        <p class="text-secondary mb-4">
            Não encontrou o tipo de serviço que procura? Sugira uma categoria.
            Ela aparece para todos depois de aprovada pelo administrador.
        </p>

        <?php if ($erro): ?>
            <div class="alert alert-danger" role="alert"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="post" class="cartao p-4">
            <?= csrfCampo() ?>

            <div class="mb-3">
                <label class="form-label" for="nome">Nome da categoria</label>
                <input class="form-control" id="nome" name="nome" maxlength="100" value="<?= e($nome) ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label" for="descricao">Descrição (opcional)</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="3" maxlength="500"><?= e($descricao) ?></textarea>
            </div>

            <button class="btn btn-primary" type="submit">Enviar sugestão</button>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
