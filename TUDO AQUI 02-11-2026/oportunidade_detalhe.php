<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/iniciar.php';
require_once __DIR__ . '/../src/Candidatura.php';

$idOp = (int) ($_GET['id'] ?? 0);
$u = usuarioLogado();
$tipoUsuario = $u['tipo'] ?? '';
$podeCandidatar = in_array($tipoUsuario, ['candidato', 'trabalhador'], true);

$erro = '';
$mensagem = '';
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$o = null;
try {
    $o = Oportunidades::buscarPorId($idOp);
} catch (Throwable $ex) {
    error_log('oportunidade_detalhe.php: ' . $ex->getMessage());
    $o = null;
}

// ---------- Ações (candidatar / retirar candidatura) ----------
if ($o && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$u) {
        header('Location: login.php');
        exit;
    }

    if (!csrfValido($_POST['csrf'] ?? '')) {
        $erro = 'Sessão expirada. Atualize a página e tente novamente.';
    } elseif (!$podeCandidatar) {
        $erro = 'Somente candidatos e trabalhadores podem se candidatar.';
    } else {
        $acao = (string) ($_POST['acao'] ?? '');

        try {
            if ($acao === 'candidatar') {
                $mensagem = trim((string) ($_POST['mensagem'] ?? ''));

                if (mb_strlen($mensagem) > 1000) {
                    $erro = 'A mensagem pode ter no máximo 1000 caracteres.';
                } else {
                    $resultado = Candidatura::candidatar($idOp, (int) $u['id'], $mensagem);

                    if ($resultado === 'ok') {
                        $_SESSION['flash'] = 'Candidatura enviada! Acompanhe em "Minhas candidaturas".';
                        header('Location: oportunidade_detalhe.php?id=' . $idOp);
                        exit;
                    }

                    $erro = $resultado === 'duplicada'
                        ? 'Você já se candidatou a esta oportunidade.'
                        : 'Esta oportunidade não está mais disponível.';
                }
            } elseif ($acao === 'retirar') {
                $existente = Candidatura::daOportunidade($idOp, (int) $u['id']);

                if (
                    $existente
                    && Candidatura::retirar((int) $existente['id_candidatura'], (int) $u['id'])
                ) {
                    $_SESSION['flash'] = 'Candidatura retirada.';
                    header('Location: oportunidade_detalhe.php?id=' . $idOp);
                    exit;
                }

                $erro = 'Candidatura não encontrada.';
            }
        } catch (Throwable $ex) {
            error_log('oportunidade_detalhe.php: ' . $ex->getMessage());
            $erro = 'Não foi possível concluir a ação. Tente novamente.';
        }
    }
}

// ---------- Situação do usuário em relação a esta oportunidade ----------
$minha = null;
$ehDono = false;

if ($o && $u) {
    $ehDono = $tipoUsuario === 'contratante'
        && (int) $u['id'] === (int) $o['id_contratante'];

    if ($podeCandidatar) {
        try {
            $minha = Candidatura::daOportunidade($idOp, (int) $u['id']);
        } catch (Throwable $ex) {
            error_log('oportunidade_detalhe.php: ' . $ex->getMessage());
        }
    }
}

// O contato da empresa só aparece para o dono da vaga ou depois de aceito.
$veContato = $ehDono || ($minha && $minha['status'] === 'aceita');

if (!$o) {
    http_response_code(404);
}

$titulo = ($o ? $o['titulo'] : 'Oportunidade não encontrada') . ' – LocalWorks';
$paginaAtiva = 'oportunidades';
require_once __DIR__ . '/../includes/cabecalho.php';
?>

<section class="secao">
    <div class="container" style="max-width: 46rem;">
        <a href="oportunidades.php" class="d-inline-block mb-3">
            <i class="bi bi-arrow-left"></i> Voltar às oportunidades
        </a>

        <?php if ($flash): ?>
            <div class="alert alert-success" role="alert"><?= e($flash) ?></div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert alert-danger" role="alert"><?= e($erro) ?></div>
        <?php endif; ?>

        <?php if (!$o): ?>
            <div class="alert alert-warning" role="alert">
                Oportunidade não encontrada ou não está mais disponível.
            </div>
        <?php else: ?>
            <div class="cartao p-4">
                <h1 class="h3 fw-bold mb-2"><?= e($o['titulo']) ?></h1>

                <p class="mb-3">
                    <span class="badge text-bg-primary"><?= e($o['categoria']) ?></span>
                    <span class="small text-secondary ms-2">
                        <i class="bi bi-geo-alt"></i>
                        <?= e($o['localizacao'] ?: 'Local não informado') ?>
                    </span>
                </p>

                <p class="small text-secondary">
                    <i class="bi bi-building"></i>
                    Publicada por <strong><?= e($o['empresa'] ?: $o['contratante']) ?></strong>
                </p>

                <p class="mb-3">
                    <i class="bi bi-cash-coin"></i>
                    <strong>Salário:</strong>
                    <?= e(formatarValor($o['valor_proposto'] ?? null)) ?>
                    <?php if (!empty($o['data_publicacao'])): ?>
                        <span class="small text-secondary ms-2">
                            <i class="bi bi-clock"></i>
                            publicada <?= e(tempoDecorrido($o['data_publicacao'])) ?>
                        </span>
                    <?php endif; ?>
                </p>

                <p>
                    <?= nl2br(e($o['descricao'] ?: 'O contratante não informou uma descrição.')) ?>
                </p>

                <hr>

                <?php if (!$u): ?>
                    <!-- Visitante -->
                    <p class="mb-2">
                        <i class="bi bi-lock"></i>
                        <a href="login.php">Entre</a> ou
                        <a href="cadastro.php?tipo=candidato">cadastre-se como candidato</a>
                        para se candidatar a esta vaga.
                    </p>
                    <p class="small text-secondary mb-0">
                        Presta serviços por conta própria?
                        <a href="cadastro.php?tipo=trabalhador">Cadastre-se como trabalhador</a>.
                    </p>

                <?php elseif ($ehDono): ?>
                    <!-- Contratante dono da vaga -->
                    <p class="mb-2 text-secondary">Esta é uma oportunidade sua.</p>
                    <a class="btn btn-primary btn-sm" href="candidatos.php?id=<?= (int) $o['id_oportunidade'] ?>">
                        <i class="bi bi-people"></i> Ver candidatos
                    </a>

                <?php elseif ($podeCandidatar): ?>
                    <!-- Candidato ou trabalhador -->
                    <?php if ($minha): ?>
                        <p class="mb-2">
                            <strong>Sua candidatura:</strong>
                            <span class="badge <?= e(Candidatura::classe($minha['status'])) ?>">
                                <?= e(Candidatura::rotulo($minha['status'])) ?>
                            </span>
                            <span class="small text-secondary ms-2">
                                enviada em <?= e(date('d/m/Y', strtotime($minha['data_candidatura']))) ?>
                            </span>
                        </p>

                        <?php if ($veContato): ?>
                            <p class="mb-3">
                                <strong>Contato da empresa:</strong>
                                <i class="bi bi-envelope"></i> <?= e($o['email']) ?>
                                <?php if ($o['telefone']): ?>
                                    · <i class="bi bi-telephone"></i> <?= e($o['telefone']) ?>
                                <?php endif; ?>
                            </p>
                        <?php else: ?>
                            <p class="small text-secondary">
                                O contato da empresa é liberado quando sua candidatura for aceita.
                            </p>
                        <?php endif; ?>

                        <form method="post" action="oportunidade_detalhe.php?id=<?= (int) $o['id_oportunidade'] ?>" class="m-0">
                            <?= csrfCampo() ?>
                            <input type="hidden" name="acao" value="retirar">
                            <button
                                class="btn btn-outline-danger btn-sm"
                                type="submit"
                                onclick="return confirm('Deseja retirar sua candidatura?');"
                            >
                                <i class="bi bi-x-circle"></i> Retirar candidatura
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="post" action="oportunidade_detalhe.php?id=<?= (int) $o['id_oportunidade'] ?>">
                            <?= csrfCampo() ?>
                            <input type="hidden" name="acao" value="candidatar">
                            <div class="mb-3">
                                <label class="form-label" for="mensagem">
                                    Mensagem para a empresa (opcional)
                                </label>
                                <textarea
                                    class="form-control"
                                    id="mensagem"
                                    name="mensagem"
                                    rows="4"
                                    maxlength="1000"
                                    placeholder="Conte rapidamente sua experiência e por que se interessa pela vaga."
                                ><?= e($mensagem) ?></textarea>
                            </div>
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-send"></i> Candidatar-me
                            </button>
                        </form>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Contratante (de outra vaga) ou administrador -->
                    <p class="mb-0 text-secondary">
                        Somente candidatos e trabalhadores podem se candidatar às oportunidades.
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
