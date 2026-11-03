<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/iniciar.php';
require_once __DIR__ . '/../src/Candidatura.php';

exigirLogin();

$u = usuarioLogado();
$tipo = $u['tipo'];
$id = (int) $u['id'];

// Quem procura emprego tem painel próprio
if ($tipo === 'candidato') {
    header('Location: painel_candidato.php');
    exit;
}

$erros = [];
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

function voltar(string $mensagem): never
{
    $_SESSION['flash'] = $mensagem;
    header('Location: painel.php');
    exit;
}

function campo(
    string $id,
    string $rotulo,
    string $valor = '',
    string $extra = ''
): string {
    return '<div class="mb-3">
        <label class="form-label" for="' . e($id) . '">' . e($rotulo) . '</label>
        <input class="form-control"
               id="' . e($id) . '"
               name="' . e($id) . '"
               value="' . e($valor) . '"
               ' . $extra . '
        >
    </div>';
}

function opcoes(array $categorias): string
{
    $html = '<option value="">Selecione…</option>';
    foreach ($categorias as $categoria) {
        $html .= '<option value="' . (int) $categoria['id_categoria'] . '">'
            . e($categoria['nome'])
            . '</option>';
    }
    return $html;
}

$categorias = [];
try {
    $categorias = Profissionais::listarCategorias();
} catch (Throwable $ex) {
    $erros[] = 'Não foi possível carregar os dados agora.';
}

$idsCat = array_map(
    'intval',
    array_column($categorias, 'id_categoria')
);

$p = fn(string $campo): string => trim(
    (string) ($_POST[$campo] ?? '')
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf'] ?? '')) {
        $erros[] = 'Sessão expirada. Atualize a página e tente novamente.';
    } else {
        $acao = $p('acao');

        try {
            if ($acao === 'publicar' && $tipo === 'contratante') {
                $tituloOp = $p('titulo');
                $descricaoOp = $p('descricao');
                $local = $p('local');
                $idCategoria = (int) $p('categoria');
                $salarioTexto = str_replace(',', '.', $p('valor'));

                $salarioInvalido = $salarioTexto !== ''
                    && (
                        !is_numeric($salarioTexto)
                        || (float) $salarioTexto < 0
                        || (float) $salarioTexto > 99999999.99
                    );

                if (
                    $tituloOp === ''
                    || $local === ''
                    || !in_array($idCategoria, $idsCat, true)
                ) {
                    $erros[] = 'Informe título, local e categoria.';
                } elseif ($salarioInvalido) {
                    $erros[] = 'Informe o salário só com números (ex.: 3200,00) ou deixe em branco.';
                } else {
                    Painel::publicarOportunidade(
                        $id,
                        $tituloOp,
                        $descricaoOp,
                        $local,
                        $idCategoria,
                        $salarioTexto === '' ? null : (float) $salarioTexto
                    );
                    voltar('Oportunidade publicada com sucesso!');
                }
            } elseif ($acao === 'remover' && $tipo === 'contratante') {
                Painel::removerOportunidade((int) $p('id'), $id);
                voltar('Oportunidade removida.');
            } elseif ($acao === 'servico' && $tipo === 'trabalhador') {
                $descricaoServ = $p('descricao');
                $valor = (float) $p('valor');
                $idCategoria = (int) $p('categoria');
                $data = DateTime::createFromFormat(
                    'Y-m-d',
                    $p('data')
                );

                $dataValida = $data
                    && $data->format('Y-m-d') === $p('data')
                    && $data->format('Y-m-d') <= date('Y-m-d');

                if (
                    $descricaoServ === ''
                    || $valor <= 0
                    || !$dataValida
                    || !in_array($idCategoria, $idsCat, true)
                ) {
                    $erros[] = 'Informe descrição, valor, uma data válida até hoje e uma categoria.';
                } else {
                    Painel::registrarServico(
                        $id,
                        $descricaoServ,
                        $valor,
                        $data->format('Y-m-d'),
                        $idCategoria
                    );
                    voltar('Serviço registrado no seu histórico!');
                }
            } elseif ($acao === 'remover_servico' && $tipo === 'trabalhador') {
                $idServico = (int) $p('id_servico');

                if ($idServico <= 0) {
                    $erros[] = 'Serviço inválido.';
                } else {
                    $removido = Painel::removerServico($idServico, $id);

                    if ($removido) {
                        voltar('Serviço removido do seu histórico.');
                    }

                    $erros[] = 'Serviço não encontrado ou você não tem permissão para removê-lo.';
                }
            } elseif ($acao === 'dados' && $tipo !== 'administrador') {
                $dados = [
                    'nome' => $p('nome'),
                    'telefone' => $p('telefone'),
                    'area' => $p('area'),
                    'descricao' => $p('descricao'),
                    'empresa' => $p('empresa'),
                ];

                $dadosInvalidos = $dados['nome'] === ''
                    || (
                        $tipo === 'trabalhador'
                        && $dados['area'] === ''
                    )
                    || (
                        $tipo === 'contratante'
                        && $dados['empresa'] === ''
                    );

                if ($dadosInvalidos) {
                    $erros[] = 'Preencha todos os campos obrigatórios.';
                } else {
                    Painel::atualizarDados($id, $tipo, $dados);
                    $_SESSION['usuario']['nome'] = $dados['nome'];
                    voltar('Dados atualizados com sucesso!');
                }
            } elseif ($acao === 'aprovar' && $tipo === 'administrador') {
                Painel::aprovarCategoria((int) $p('id'));
                voltar('Categoria aprovada.');
            }
        } catch (Throwable $ex) {
            $erros[] = 'Não foi possível salvar os dados. Tente novamente.';
        }
    }
}

$meus = [
    'nome' => '',
    'telefone' => '',
    'empresa' => '',
    'area_atendimento' => '',
    'descricao_perfil' => '',
];

$lista = [];
$rel = [];
$contagem = [];

try {
    if ($tipo !== 'administrador') {
        $meus = Painel::meusDados($id) ?: $meus;
    }

    if ($tipo === 'contratante') {
        $lista = Painel::minhasOportunidades($id);
        $contagem = Candidatura::contagemPorOportunidade($id);
    }

    if ($tipo === 'trabalhador') {
        $lista = Profissionais::listarServicos($id);
    }

    if ($tipo === 'administrador') {
        $rel = Painel::relatorio();
        $lista = Painel::categoriasPendentes();
    }
} catch (Throwable $ex) {
    $erros[] = 'Não foi possível carregar o painel agora.';
}

$titulo = 'Meu painel – LocalWorks';
require_once __DIR__ . '/../includes/cabecalho.php';
?>

<section class="secao">
    <div class="container">
        <h1 class="h2 fw-bold">
            Olá, <?= e($u['nome']) ?>!
        </h1>

        <p class="text-secondary">
            Painel do <?= e($tipo) ?>
            <?php if ($tipo !== 'administrador'): ?>
                · <a href="sugerir_categoria.php">Sugerir nova categoria</a>
            <?php endif; ?>
        </p>

        <?php if ($flash): ?>
            <div class="alert alert-success" role="alert">
                <?= e($flash) ?>
            </div>
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

        <?php if ($tipo === 'administrador'): ?>
            <div class="row g-3 mb-4">
                <?php foreach ([
                    'trabalhadores' => 'Trabalhadores',
                    'contratantes' => 'Contratantes',
                    'oportunidades' => 'Oportunidades publicadas',
                    'servicos' => 'Serviços realizados',
                    'categorias' => 'Categorias aprovadas',
                ] as $chave => $rotulo): ?>
                    <div class="col-6 col-lg">
                        <div class="cartao p-3 text-center">
                            <div class="h3 fw-bold text-primary mb-0">
                                <?= (int) ($rel[$chave] ?? 0) ?>
                            </div>
                            <div class="small text-secondary">
                                <?= e($rotulo) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cartao p-4 mb-4">
                <strong>Valor movimentado em serviços:</strong>
                R$ <?= e(number_format(
                    (float) ($rel['valor_total'] ?? 0),
                    2,
                    ',',
                    '.'
                )) ?>
            </div>

            <h2 class="h4 fw-bold mb-3">
                Categorias pendentes
            </h2>

            <?php if (!$lista): ?>
                <p class="text-secondary">
                    Nenhuma categoria aguardando aprovação.
                </p>
            <?php endif; ?>

            <?php foreach ($lista as $categoria): ?>
                <form
                    method="post"
                    class="cartao p-3 mb-2 d-flex justify-content-between align-items-center"
                >
                    <?= csrfCampo() ?>
                    <input type="hidden" name="acao" value="aprovar">
                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int) $categoria['id_categoria'] ?>"
                    >
                    <span><?= e($categoria['nome']) ?></span>
                    <button class="btn btn-primary btn-sm" type="submit">
                        Aprovar
                    </button>
                </form>
            <?php endforeach; ?>

        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-6">
                    <?php if ($tipo === 'contratante'): ?>
                        <form method="post" class="cartao p-4 mb-4">
                            <?= csrfCampo() ?>
                            <input type="hidden" name="acao" value="publicar">

                            <h2 class="h5 fw-bold mb-3">
                                Publicar oportunidade
                            </h2>

                            <?= campo('titulo', 'Título', '', 'required maxlength="100"') ?>

                            <div class="mb-3">
                                <label class="form-label" for="descricao">
                                    Descrição
                                </label>
                                <textarea
                                    class="form-control"
                                    id="descricao"
                                    name="descricao"
                                    rows="3"
                                ></textarea>
                            </div>

                            <?= campo('local', 'Local (cidade ou bairro)', '', 'required maxlength="100"') ?>

                            <?= campo(
                                'valor',
                                'Salário (R$, opcional)',
                                '',
                                'inputmode="decimal" maxlength="12" placeholder="Ex.: 3200,00"'
                            ) ?>

                            <div class="mb-3">
                                <label class="form-label" for="categoria">
                                    Categoria
                                </label>
                                <select
                                    class="form-select"
                                    id="categoria"
                                    name="categoria"
                                    required
                                >
                                    <?= opcoes($categorias) ?>
                                </select>
                            </div>

                            <button class="btn btn-primary" type="submit">
                                Publicar
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="post" class="cartao p-4 mb-4">
                            <?= csrfCampo() ?>
                            <input type="hidden" name="acao" value="servico">

                            <h2 class="h5 fw-bold mb-3">
                                Registrar serviço realizado
                            </h2>

                            <?= campo('descricao', 'Descrição do serviço', '', 'required maxlength="500"') ?>

                            <?= campo(
                                'valor',
                                'Valor (R$)',
                                '',
                                'type="number" step="0.01" min="0.01" required'
                            ) ?>

                            <?= campo(
                                'data',
                                'Data',
                                date('Y-m-d'),
                                'type="date" max="' . date('Y-m-d') . '" required'
                            ) ?>

                            <div class="mb-3">
                                <label class="form-label" for="categoria">
                                    Categoria
                                </label>
                                <select
                                    class="form-select"
                                    id="categoria"
                                    name="categoria"
                                    required
                                >
                                    <?= opcoes($categorias) ?>
                                </select>
                            </div>

                            <button class="btn btn-primary" type="submit">
                                Registrar
                            </button>
                        </form>
                    <?php endif; ?>

                    <form method="post" class="cartao p-4">
                        <?= csrfCampo() ?>
                        <input type="hidden" name="acao" value="dados">

                        <h2 class="h5 fw-bold mb-3">
                            Meus dados
                        </h2>

                        <?= campo(
                            'nome',
                            'Nome',
                            (string) $meus['nome'],
                            'required maxlength="100"'
                        ) ?>

                        <?= campo(
                            'telefone',
                            'Telefone',
                            (string) $meus['telefone'],
                            'maxlength="20"'
                        ) ?>

                        <?php if ($tipo === 'trabalhador'): ?>
                            <?= campo(
                                'area',
                                'Área de atendimento',
                                (string) $meus['area_atendimento'],
                                'required maxlength="100"'
                            ) ?>

                            <div class="mb-3">
                                <label class="form-label" for="descricao_d">
                                    Apresentação
                                </label>
                                <textarea
                                    class="form-control"
                                    id="descricao_d"
                                    name="descricao"
                                    rows="5"
                                    maxlength="1000"
                                ><?= e(trim((string) ($meus['descricao_perfil'] ?? ''))) ?></textarea>
                            </div>
                        <?php else: ?>
                            <?= campo(
                                'empresa',
                                'Empresa',
                                (string) $meus['empresa'],
                                'required maxlength="100"'
                            ) ?>
                        <?php endif; ?>

                        <button class="btn btn-primary" type="submit">
                            Salvar dados
                        </button>
                    </form>
                </div>

                <div class="col-lg-6">
                    <?php if ($tipo === 'contratante'): ?>
                        <h2 class="h5 fw-bold mb-3">
                            Minhas oportunidades
                        </h2>

                        <?php if (!$lista): ?>
                            <p class="text-secondary">
                                Você ainda não publicou oportunidades.
                            </p>
                        <?php endif; ?>

                        <?php foreach ($lista as $oportunidade): ?>
                            <form
                                method="post"
                                class="cartao p-3 mb-2 d-flex justify-content-between align-items-center gap-2"
                            >
                                <?= csrfCampo() ?>
                                <input type="hidden" name="acao" value="remover">
                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $oportunidade['id_oportunidade'] ?>"
                                >

                                <span>
                                    <strong><?= e($oportunidade['titulo']) ?></strong>
                                    <br>
                                    <span class="small text-secondary">
                                        <?= e($oportunidade['categoria']) ?> ·
                                        <?= e($oportunidade['localizacao']) ?> ·
                                        <?= $oportunidade['publicada'] ? 'Publicada' : 'Removida' ?>
                                    </span>
                                </span>

                                <div class="d-flex gap-2">
                                    <a
                                        class="btn btn-primary btn-sm"
                                        href="candidatos.php?id=<?= (int) $oportunidade['id_oportunidade'] ?>"
                                    >
                                        Candidatos (<?= (int) ($contagem[$oportunidade['id_oportunidade']] ?? 0) ?>)
                                    </a>

                                    <?php if ($oportunidade['publicada']): ?>
                                        <button
                                            class="btn btn-outline-primary btn-sm"
                                            type="submit"
                                            onclick="return confirm('Deseja remover esta oportunidade?');"
                                        >
                                            Remover
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
                            <h2 class="h5 fw-bold mb-0">
                                Meu histórico
                            </h2>

                            <div class="d-flex flex-wrap gap-2">
                                <a
                                    class="btn btn-outline-primary btn-sm"
                                    href="minhas_candidaturas.php"
                                >
                                    <i class="bi bi-send-check"></i>
                                    Minhas candidaturas
                                </a>
                                <a
                                    class="btn btn-outline-primary btn-sm"
                                    href="perfil.php?id=<?= (int) $id ?>"
                                >
                                    <i class="bi bi-person-vcard"></i>
                                    Ver meu perfil público
                                </a>
                            </div>
                        </div>

                        <?php if (!$lista): ?>
                            <p class="text-secondary">
                                Você ainda não registrou serviços.
                            </p>
                        <?php endif; ?>

                        <ul class="list-group">
                            <?php foreach ($lista as $servico): ?>
                                <li class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                                    <div>
                                        <strong><?= e($servico['descricao']) ?></strong>
                                        <span class="badge text-bg-light">
                                            <?= e($servico['categoria']) ?>
                                        </span>
                                        <div class="small text-secondary mt-1">
                                            <?= e(date(
                                                'd/m/Y',
                                                strtotime($servico['data_servico'])
                                            )) ?>
                                            · R$ <?= e(number_format(
                                                (float) $servico['valor'],
                                                2,
                                                ',',
                                                '.'
                                            )) ?>
                                        </div>
                                    </div>

                                    <form method="post" class="m-0">
                                        <?= csrfCampo() ?>
                                        <input
                                            type="hidden"
                                            name="acao"
                                            value="remover_servico"
                                        >
                                        <input
                                            type="hidden"
                                            name="id_servico"
                                            value="<?= (int) $servico['id_servico'] ?>"
                                        >
                                        <button
                                            class="btn btn-outline-danger btn-sm"
                                            type="submit"
                                            onclick="return confirm('Deseja realmente remover este serviço do histórico?');"
                                        >
                                            <i class="bi bi-trash"></i>
                                            Remover
                                        </button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
