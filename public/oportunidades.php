<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/iniciar.php';

$idCategoria = (int) ($_GET['categoria'] ?? 0);
$local = trim((string) ($_GET['local'] ?? ''));
$categorias = [];
$oportunidades = [];
$erro = '';

try {
    $categorias = Profissionais::listarCategorias();
    $oportunidades = Oportunidades::buscar($idCategoria, $local);
} catch (Throwable $ex) {
    error_log('oportunidades.php: ' . $ex->getMessage());
    $erro = 'Não foi possível carregar as oportunidades agora.';
}

$titulo = 'Oportunidades – LocalWorks';
$paginaAtiva = 'oportunidades';
require_once __DIR__ . '/../includes/cabecalho.php';
?>

<section class="secao">
    <div class="container">
        <h1 class="h2 fw-bold mb-2">Oportunidades</h1>
        <p class="text-secondary mb-4">
            Serviços e vagas publicados por contratantes e pequenos negócios da sua região.
        </p>

        <form method="get" action="oportunidades.php" class="cartao p-3 mb-4">
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
            <div class="alert alert-danger" role="alert"><?= e($erro) ?></div>
        <?php elseif (!$oportunidades): ?>
            <p class="text-secondary">Nenhuma oportunidade encontrada com esses filtros.</p>
        <?php else: ?>
            <p class="text-secondary">
                <?= count($oportunidades) ?> oportunidade(s) encontrada(s).
            </p>

            <div class="row g-4">
                <?php
                $hTag = 'h2';
                foreach ($oportunidades as $o) {
                    include __DIR__ . '/../includes/cartao_proposta.php';
                }
                ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
