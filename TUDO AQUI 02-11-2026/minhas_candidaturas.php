<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/iniciar.php';
require_once __DIR__ . '/../src/Candidatura.php';

exigirLogin();

$u = usuarioLogado();
if (!in_array($u['tipo'], ['candidato', 'trabalhador'], true)) {
    header('Location: painel.php');
    exit;
}

$id = (int) $u['id'];
$erro = '';
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// ---------- Retirar candidatura ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf'] ?? '')) {
        $erro = 'Sessão expirada. Atualize a página e tente novamente.';
    } else {
        try {
            $idCandidatura = (int) ($_POST['id_candidatura'] ?? 0);

            if ($idCandidatura > 0 && Candidatura::retirar($idCandidatura, $id)) {
                $_SESSION['flash'] = 'Candidatura retirada.';
                header('Location: minhas_candidaturas.php');
                exit;
            }
            $erro = 'Candidatura não encontrada.';
        } catch (Throwable $ex) {
            error_log('minhas_candidaturas.php: ' . $ex->getMessage());
            $erro = 'Não foi possível retirar a candidatura.';
        }
    }
}

$lista = [];
try {
    $lista = Candidatura::minhas($id);
} catch (Throwable $ex) {
    error_log('minhas_candidaturas.php: ' . $ex->getMessage());
    $erro = $erro ?: 'Não foi possível carregar suas candidaturas agora.';
}

$titulo = 'Minhas candidaturas – LocalWorks';
$paginaAtiva = 'candidaturas';
require_once __DIR__ . '/../includes/cabecalho.php';
?>

<section class="secao">
    <div class="container" style="max-width: 52rem;">
        <h1 class="h2 fw-bold mb-2">Minhas candidaturas</h1>
        <p class="text-secondary mb-4">
            Acompanhe as oportunidades a que você se candidatou. O contato da empresa
            é liberado quando a candidatura é aceita.
        </p>

        <?php if ($flash): ?>
            <div class="alert alert-success" role="alert"><?= e($flash) ?></div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert alert-danger" role="alert"><?= e($erro) ?></div>
        <?php endif; ?>

        <?php if (!$lista && !$erro): ?>
            <p class="text-secondary">
                Você ainda não se candidatou a nenhuma oportunidade.
                <a href="oportunidades.php">Ver oportunidades</a>.
            </p>
        <?php endif; ?>

        <ul class="list-group">
            <?php foreach ($lista as $c): ?>
                <li class="list-group-item">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                        <div>
                            <strong><?= e($c['titulo']) ?></strong>
                            <span class="badge <?= e(Candidatura::classe($c['status'])) ?> ms-1">
                                <?= e(Candidatura::rotulo($c['status'])) ?>
                            </span>
                            <?php if (!$c['publicada']): ?>
                                <span class="badge text-bg-light">Oportunidade encerrada</span>
                            <?php endif; ?>

                            <div class="small text-secondary mt-1">
                                <i class="bi bi-building"></i> <?= e($c['empresa'] ?: 'Contratante') ?>
                                · <i class="bi bi-geo-alt"></i> <?= e($c['localizacao'] ?: 'Local não informado') ?>
                                · enviada em <?= e(date('d/m/Y', strtotime($c['data_candidatura']))) ?>
                            </div>

                            <?php if ($c['status'] === 'aceita'): ?>
                                <div class="mt-2">
                                    <strong>Contato da empresa:</strong>
                                    <i class="bi bi-envelope"></i> <?= e($c['contato_email']) ?>
                                    <?php if ($c['contato_telefone']): ?>
                                        · <i class="bi bi-telephone"></i> <?= e($c['contato_telefone']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex gap-2 align-items-start">
                            <?php if ($c['publicada']): ?>
                                <a class="btn btn-outline-primary btn-sm" href="oportunidade_detalhe.php?id=<?= (int) $c['id_oportunidade'] ?>">
                                    Ver vaga
                                </a>
                            <?php endif; ?>

                            <form method="post" class="m-0">
                                <?= csrfCampo() ?>
                                <input type="hidden" name="id_candidatura" value="<?= (int) $c['id_candidatura'] ?>">
                                <button
                                    class="btn btn-outline-danger btn-sm"
                                    type="submit"
                                    onclick="return confirm('Deseja retirar esta candidatura?');"
                                >
                                    <i class="bi bi-x-circle"></i> Retirar
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
