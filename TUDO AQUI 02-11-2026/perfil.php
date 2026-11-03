<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/iniciar.php';

$perfil = null;
$servicos = [];

try {
    $perfil = Profissionais::buscarPorId((int) ($_GET['id'] ?? 0));

    if ($perfil) {
        $servicos = Profissionais::listarServicos((int) $perfil['id_usuario']);
    }
} catch (Throwable $ex) {
    error_log('perfil.php: ' . $ex->getMessage());
    $perfil = null;
}

if (!$perfil) {
    http_response_code(404);
}

$titulo = ($perfil ? $perfil['nome'] : 'Perfil não encontrado') . ' – LocalWorks';
$paginaAtiva = 'buscar';
require_once __DIR__ . '/../includes/cabecalho.php';
?>

<section class="secao">
    <div class="container" style="max-width: 46rem;">
        <a href="buscar.php" class="d-inline-block mb-3">
            <i class="bi bi-arrow-left"></i> Voltar à busca
        </a>

        <?php if (!$perfil): ?>
            <div class="alert alert-warning">Profissional não encontrado.</div>
        <?php else: ?>
            <div class="cartao p-4 mb-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="icone fw-bold">
                        <?= e(mb_strtoupper(mb_substr($perfil['nome'], 0, 1))) ?>
                    </span>

                    <div>
                        <h1 class="h3 fw-bold mb-1"><?= e($perfil['nome']) ?></h1>

                        <span class="badge text-bg-primary">
                            <?= e($perfil['especialidade'] ?: 'Profissional') ?>
                        </span>

                        <span class="small text-secondary ms-2">
                            <i class="bi bi-geo-alt"></i>
                            <?= e($perfil['area_atendimento'] ?: 'Área não informada') ?>
                        </span>
                    </div>
                </div>

                <p>
                    <?= e($perfil['descricao_perfil'] ?: 'Este profissional ainda não escreveu uma apresentação.') ?>
                </p>

                <?php if (usuarioLogado()): ?>
                    <p class="mb-0">
                        <i class="bi bi-envelope"></i> <?= e($perfil['email']) ?>
                        <?php if ($perfil['telefone']): ?>
                            · <i class="bi bi-telephone"></i> <?= e($perfil['telefone']) ?>
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <p class="mb-0 text-secondary">
                        <i class="bi bi-lock"></i>
                        <a href="login.php">Entre</a> para ver os contatos.
                    </p>
                <?php endif; ?>
            </div>

            <h2 class="h4 fw-bold mb-3">Histórico de serviços</h2>

            <?php if (!$servicos): ?>
                <p class="text-secondary">Este profissional ainda não registrou serviços.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($servicos as $s): ?>
                        <li class="list-group-item d-flex justify-content-between gap-3">
                            <span>
                                <?= e($s['descricao']) ?>
                                <span class="badge text-bg-light"><?= e($s['categoria']) ?></span>
                            </span>

                            <span class="small text-secondary text-nowrap">
                                <?= e(date('d/m/Y', strtotime($s['data_servico']))) ?> ·
                                R$ <?= e(number_format((float) $s['valor'], 2, ',', '.')) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
