<?php
// Card de uma proposta. Espera a variável $o definida pelo loop que o inclui.
// Opcional: defina $hTag ('h2' ou 'h3') antes do loop para ajustar o nível do título.
$hTag = $hTag ?? 'h3';
?>
<div class="col-md-6 col-lg-4">
    <div class="cartao bg-white h-100 p-4 d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
            <span class="badge text-bg-primary"><?= e($o['categoria']) ?></span>
            <span class="small text-secondary text-nowrap">
                <i class="bi bi-clock"></i>
                <?= e(tempoDecorrido($o['data_publicacao'] ?? null)) ?>
            </span>
        </div>

        <<?= $hTag ?> class="h5 fw-bold mb-1"><?= e($o['titulo']) ?></<?= $hTag ?>>

        <p class="small text-secondary mb-1">
            <i class="bi bi-building"></i> <?= e($o['empresa'] ?: 'Contratante') ?>
        </p>
        <p class="small text-secondary mb-2">
            <i class="bi bi-geo-alt"></i> <?= e($o['localizacao'] ?: 'Local não informado') ?>
        </p>
        <p class="fw-semibold text-primary mb-2">
            <i class="bi bi-cash-coin"></i> <?= e(formatarValor($o['valor_proposto'] ?? null)) ?>
        </p>
        <p class="mb-3">
            <?= e(mb_strimwidth((string) $o['descricao'], 0, 100, '…')) ?>
        </p>

        <a class="btn btn-outline-primary btn-sm mt-auto align-self-start"
           href="oportunidade_detalhe.php?id=<?= (int) $o['id_oportunidade'] ?>">
            Ver detalhes
        </a>
    </div>
</div>
