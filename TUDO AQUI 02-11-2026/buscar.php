<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/iniciar.php';

$idCategoria = (int) ($_GET['categoria'] ?? 0);
$local = trim((string) ($_GET['local'] ?? ''));
$categorias = [];
$profissionais = [];
$erro = '';

try {
    $categorias = Profissionais::listarCategorias();
    $profissionais = Profissionais::buscar($idCategoria, $local);
} catch (Throwable $ex) {
    error_log('buscar.php: ' . $ex->getMessage());
    $erro = 'Não foi possível carregar a busca agora.';
}

$titulo = 'Buscar profissionais – LocalWorks';
$paginaAtiva = 'buscar';
require_once __DIR__ . '/../includes/cabecalho.php';
?>

<section class="secao">
    <div class="container">
        <h1 class="h2 fw-bold mb-2">Buscar profissionais</h1>
        <p class="text-secondary mb-4">
            Procurando uma vaga de emprego?
            <a href="oportunidades.php">Veja as oportunidades</a>.
        </p>

        <form method="get" action="buscar.php" class="cartao p-3 mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label" for="categoria">Categoria</label>
                    <select class="form-select" id="categoria" name="categoria">
                        <option value="0">Todas as categorias</option>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= (int) $c['id_categoria'] ?>"
                                <?= $idCategoria === (int) $c['id_categoria'] ? 'selected' : '' ?>>
                                <?= e($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label" for="local">Cidade ou bairro</label>
                    <input class="form-control" id="local" name="local" value="<?= e($local) ?>" placeholder="Ex.: Centro">
                </div>

                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </div>
        </form>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= e($erro) ?></div>
        <?php elseif (!$profissionais): ?>
            <p class="text-secondary">Nenhum profissional encontrado com esses filtros.</p>
        <?php else: ?>
            <p class="text-secondary">
                <?= count($profissionais) ?> profissional(is) encontrado(s).
            </p>

            <div class="row g-4">
                <?php foreach ($profissionais as $p): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="cartao h-100 p-4">
                            <h2 class="h5 fw-bold mb-1"><?= e($p['nome']) ?></h2>

                            <span class="badge text-bg-primary mb-2">
                                <?= e($p['especialidade'] ?: 'Profissional') ?>
                            </span>

                            <p class="small text-secondary mb-2">
                                <i class="bi bi-geo-alt"></i>
                                <?= e($p['area_atendimento'] ?: 'Área não informada') ?>
                            </p>

                            <p class="mb-3">
                                <?= e(mb_strimwidth((string) $p['descricao_perfil'], 0, 120, '…')) ?>
                            </p>

                            <a class="btn btn-outline-primary btn-sm" href="perfil.php?id=<?= (int) $p['id_usuario'] ?>">
                                Ver perfil
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
