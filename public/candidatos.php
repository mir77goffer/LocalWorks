<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/iniciar.php';
require_once __DIR__ . '/../src/Candidatura.php';

exigirLogin();

$u = usuarioLogado();
if ($u['tipo'] !== 'contratante') {
    header('Location: painel.php');
    exit;
}

$idContratante = (int) $u['id'];
$idOp = (int) ($_GET['id'] ?? 0);

$erro = '';
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$op = null;
$candidatos = [];

try {
    $op = Candidatura::oportunidadeDoContratante($idOp, $idContratante);
} catch (Throwable $ex) {
    error_log('candidatos.php: ' . $ex->getMessage());
    $erro = 'Não foi possível carregar os candidatos agora.';
}

// ---------- Aceitar / recusar / voltar para análise ----------
if ($op && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf'] ?? '')) {
        $erro = 'Sessão expirada. Atualize a página e tente novamente.';
    } else {
        $idCandidatura = (int) ($_POST['id_candidatura'] ?? 0);
        $status = (string) ($_POST['status'] ?? '');

        try {
            if (Candidatura::definirStatus($idCandidatura, $idContratante, $status)) {
                $_SESSION['flash'] = 'Candidatura atualizada: ' . Candidatura::rotulo($status) . '.';
                header('Location: candidatos.php?id=' . $idOp);
                exit;
            }
            $erro = 'Candidatura não encontrada ou ação inválida.';
        } catch (Throwable $ex) {
            error_log('candidatos.php: ' . $ex->getMessage());
            $erro = 'Não foi possível atualizar a candidatura.';
        }
    }
}

if ($op) {
    try {
        $candidatos = Candidatura::candidatos($idOp, $idContratante);
    } catch (Throwable $ex) {
        error_log('candidatos.php: ' . $ex->getMessage());
        $erro = 'Não foi possível carregar os candidatos agora.';
    }
} elseif (!$erro) {
    http_response_code(404);
}

$titulo = 'Candidatos – LocalWorks';
require_once __DIR__ . '/../includes/cabecalho.php';
?>

<section class="secao">
    <div class="container" style="max-width: 52rem;">
        <a href="painel.php" class="d-inline-block mb-3">
            <i class="bi bi-arrow-left"></i> Voltar ao painel
        </a>

        <?php if ($flash): ?>
            <div class="alert alert-success" role="alert"><?= e($flash) ?></div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert alert-danger" role="alert"><?= e($erro) ?></div>
        <?php endif; ?>

        <?php if (!$op): ?>
            <?php if (!$erro): ?>
                <div class="alert alert-warning" role="alert">
                    Oportunidade não encontrada ou não pertence a você.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <h1 class="h3 fw-bold mb-1">Candidatos</h1>
            <p class="text-secondary mb-4">
                <?= e($op['titulo']) ?> · <?= e($op['localizacao'] ?: 'Local não informado') ?>
                <?php if (!$op['publicada']): ?>
                    <span class="badge text-bg-light">Oportunidade removida</span>
                <?php endif; ?>
            </p>

            <?php if (!$candidatos): ?>
                <p class="text-secondary">Ainda não há candidaturas para esta oportunidade.</p>
            <?php else: ?>
                <p class="text-secondary"><?= count($candidatos) ?> candidatura(s).</p>

                <?php foreach ($candidatos as $c): ?>
                    <div class="cartao bg-white p-4 mb-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
                            <div>
                                <h2 class="h5 fw-bold mb-1">
                                    <?= e($c['nome']) ?>
                                    <span class="badge <?= e(Candidatura::classe($c['status'])) ?> ms-1">
                                        <?= e(Candidatura::rotulo($c['status'])) ?>
                                    </span>
                                </h2>
                                <p class="small text-secondary mb-0">
                                    <i class="bi bi-briefcase"></i>
                                    <?= e($c['area'] ?: 'Área não informada') ?>
                                    · <i class="bi bi-geo-alt"></i>
                                    <?= e($c['cidade'] ?: 'Cidade não informada') ?>
                                </p>
                            </div>
                            <span class="small text-secondary text-nowrap">
                                <i class="bi bi-clock"></i>
                                <?= e(date('d/m/Y H:i', strtotime($c['data_candidatura']))) ?>
                            </span>
                        </div>

                        <?php if ($c['pretensao_salarial'] !== null): ?>
                            <p class="small mb-2">
                                <strong>Pretensão salarial:</strong>
                                <?= e(formatarValor($c['pretensao_salarial'])) ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($c['resumo']): ?>
                            <p class="mb-2">
                                <strong>Resumo:</strong>
                                <?= nl2br(e(mb_strimwidth((string) $c['resumo'], 0, 400, '…'))) ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($c['mensagem']): ?>
                            <p class="mb-3">
                                <strong>Mensagem:</strong>
                                <?= nl2br(e($c['mensagem'])) ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($c['status'] === 'aceita'): ?>
                            <p class="mb-3">
                                <strong>Contato:</strong>
                                <i class="bi bi-envelope"></i> <?= e($c['email']) ?>
                                <?php if ($c['telefone']): ?>
                                    · <i class="bi bi-telephone"></i> <?= e($c['telefone']) ?>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>

                        <div class="d-flex flex-wrap gap-2">
                            <?php if ($c['status'] !== 'aceita'): ?>
                                <form method="post" class="m-0">
                                    <?= csrfCampo() ?>
                                    <input type="hidden" name="id_candidatura" value="<?= (int) $c['id_candidatura'] ?>">
                                    <input type="hidden" name="status" value="aceita">
                                    <button class="btn btn-primary btn-sm" type="submit">
                                        <i class="bi bi-check-circle"></i> Aceitar
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if ($c['status'] !== 'recusada'): ?>
                                <form method="post" class="m-0">
                                    <?= csrfCampo() ?>
                                    <input type="hidden" name="id_candidatura" value="<?= (int) $c['id_candidatura'] ?>">
                                    <input type="hidden" name="status" value="recusada">
                                    <button class="btn btn-outline-danger btn-sm" type="submit">
                                        <i class="bi bi-x-circle"></i> Recusar
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if ($c['status'] !== 'enviada'): ?>
                                <form method="post" class="m-0">
                                    <?= csrfCampo() ?>
                                    <input type="hidden" name="id_candidatura" value="<?= (int) $c['id_candidatura'] ?>">
                                    <input type="hidden" name="status" value="enviada">
                                    <button class="btn btn-outline-primary btn-sm" type="submit">
                                        Voltar para análise
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if ($c['eh_trabalhador']): ?>
                                <a class="btn btn-outline-primary btn-sm" href="perfil.php?id=<?= (int) $c['id_usuario'] ?>">
                                    <i class="bi bi-person-vcard"></i> Ver perfil público
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
