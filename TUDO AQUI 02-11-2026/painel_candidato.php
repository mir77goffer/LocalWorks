<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/iniciar.php';
require_once __DIR__ . '/../src/Candidato.php';
require_once __DIR__ . '/../src/Candidatura.php';

exigirLogin();

$u = usuarioLogado();
if ($u['tipo'] !== 'candidato') {
    header('Location: painel.php');
    exit;
}

$id = (int) $u['id'];
$erros = [];
$usarPost = false;
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$meus = [
    'nome' => '',
    'telefone' => '',
    'area_interesse' => '',
    'cidade' => '',
    'resumo' => '',
    'pretensao_salarial' => null,
];

// ---------- Salvar dados ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf'] ?? '')) {
        $erros[] = 'Sessão expirada. Atualize a página e tente novamente.';
    } else {
        $nome = trim((string) ($_POST['nome'] ?? ''));
        $telefone = trim((string) ($_POST['telefone'] ?? ''));
        $areaInteresse = trim((string) ($_POST['area_interesse'] ?? ''));
        $cidade = trim((string) ($_POST['cidade'] ?? ''));
        $resumo = trim((string) ($_POST['resumo'] ?? ''));
        $pretensao = trim((string) ($_POST['pretensao'] ?? ''));

        if ($nome === '' || $areaInteresse === '') {
            $erros[] = 'Informe seu nome e a área ou profissão que você procura.';
        } elseif ($pretensao !== '' && !Candidato::valorValido($pretensao)) {
            $erros[] = 'Informe a pretensão salarial só com números (ex.: 2500,00).';
        } else {
            try {
                Candidato::atualizarDados($id, [
                    'nome' => $nome,
                    'telefone' => $telefone,
                    'area_interesse' => $areaInteresse,
                    'cidade' => $cidade,
                    'resumo' => $resumo,
                    'pretensao' => Candidato::converterValor($pretensao),
                ]);
                $_SESSION['usuario']['nome'] = $nome;
                $_SESSION['flash'] = 'Dados atualizados com sucesso!';
                header('Location: painel_candidato.php');
                exit;
            } catch (Throwable $ex) {
                error_log('painel_candidato.php: ' . $ex->getMessage());
                $erros[] = 'Não foi possível salvar os dados. Tente novamente.';
            }
        }

        // Mantém o que a pessoa digitou se der erro
        $usarPost = true;
        $meus = [
            'nome' => $nome,
            'telefone' => $telefone,
            'area_interesse' => $areaInteresse,
            'cidade' => $cidade,
            'resumo' => $resumo,
            'pretensao_salarial' => $pretensao,
        ];
    }
}

// ---------- Carregar dados e resumo das candidaturas ----------
$total = 0;
$aceitas = 0;

try {
    if (!$usarPost) {
        $meus = Candidato::meusDados($id) ?: $meus;
    }

    $candidaturas = Candidatura::minhas($id);
    $total = count($candidaturas);
    foreach ($candidaturas as $c) {
        if ($c['status'] === 'aceita') {
            $aceitas++;
        }
    }
} catch (Throwable $ex) {
    error_log('painel_candidato.php: ' . $ex->getMessage());
    $erros[] = 'Não foi possível carregar o painel agora.';
}

$pretensaoCampo = $meus['pretensao_salarial'] === null || $meus['pretensao_salarial'] === ''
    ? ''
    : str_replace('.', ',', (string) $meus['pretensao_salarial']);

$titulo = 'Meu painel – LocalWorks';
require_once __DIR__ . '/../includes/cabecalho.php';
?>

<section class="secao">
    <div class="container">
        <h1 class="h2 fw-bold">Olá, <?= e($u['nome']) ?>!</h1>
        <p class="text-secondary">Painel do candidato</p>

        <?php if ($flash): ?>
            <div class="alert alert-success" role="alert"><?= e($flash) ?></div>
        <?php endif; ?>

        <?php if ($erros): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    <?php foreach ($erros as $erro): ?>
                        <li><?= e($erro) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-6">
                <form method="post" class="cartao p-4">
                    <?= csrfCampo() ?>
                    <h2 class="h5 fw-bold mb-3">Meus dados</h2>

                    <div class="mb-3">
                        <label class="form-label" for="nome">Nome</label>
                        <input class="form-control" id="nome" name="nome" value="<?= e($meus['nome']) ?>" maxlength="100" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="telefone">Telefone</label>
                        <input class="form-control" id="telefone" name="telefone" value="<?= e($meus['telefone']) ?>" maxlength="20">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="area_interesse">Profissão ou área que você procura</label>
                        <input class="form-control" id="area_interesse" name="area_interesse" value="<?= e($meus['area_interesse']) ?>" maxlength="100" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="cidade">Cidade onde procura trabalho</label>
                        <input class="form-control" id="cidade" name="cidade" value="<?= e($meus['cidade']) ?>" maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="pretensao">Pretensão salarial (R$, opcional)</label>
                        <input class="form-control" id="pretensao" name="pretensao" value="<?= e($pretensaoCampo) ?>" maxlength="12" placeholder="Ex.: 2500,00">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="resumo">Resumo profissional</label>
                        <textarea class="form-control" id="resumo" name="resumo" rows="5" maxlength="1000"><?= e(trim((string) ($meus['resumo'] ?? ''))) ?></textarea>
                    </div>

                    <button class="btn btn-primary" type="submit">Salvar dados</button>
                </form>
            </div>

            <div class="col-lg-6">
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="cartao p-3 text-center">
                            <div class="h3 fw-bold text-primary mb-0"><?= (int) $total ?></div>
                            <div class="small text-secondary">Candidaturas enviadas</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="cartao p-3 text-center">
                            <div class="h3 fw-bold text-primary mb-0"><?= (int) $aceitas ?></div>
                            <div class="small text-secondary">Aceitas</div>
                        </div>
                    </div>
                </div>

                <div class="cartao p-4">
                    <h2 class="h5 fw-bold mb-3">Próximos passos</h2>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a class="btn btn-primary" href="oportunidades.php">
                            <i class="bi bi-search"></i> Ver oportunidades
                        </a>
                        <a class="btn btn-outline-primary" href="minhas_candidaturas.php">
                            <i class="bi bi-send-check"></i> Minhas candidaturas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
