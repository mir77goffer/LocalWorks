<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/iniciar.php';
require_once __DIR__ . '/../src/Candidato.php';

if (usuarioLogado()) {
    header('Location: painel.php');
    exit;
}

$tiposValidos = ['candidato', 'trabalhador', 'contratante'];

$tipo = in_array($_GET['tipo'] ?? '', $tiposValidos, true)
    ? (string) $_GET['tipo']
    : 'candidato';

$erro = '';
$nome = '';
$email = '';
$telefone = '';
$cpf = '';
$especialidade = '';
$area = '';
$descricao = '';
$cnpj = '';
$empresa = '';
$areaInteresse = '';
$cidade = '';
$resumo = '';
$pretensao = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = in_array($_POST['tipo'] ?? '', $tiposValidos, true)
        ? (string) $_POST['tipo']
        : 'candidato';

    $nome = trim((string) ($_POST['nome'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $telefone = trim((string) ($_POST['telefone'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');
    $confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');

    // trabalhador (presta serviços)
    $cpf = trim((string) ($_POST['cpf'] ?? ''));
    $especialidade = trim((string) ($_POST['especialidade'] ?? ''));
    $area = trim((string) ($_POST['area'] ?? ''));
    $descricao = trim((string) ($_POST['descricao'] ?? ''));

    // contratante
    $cnpj = trim((string) ($_POST['cnpj'] ?? ''));
    $empresa = trim((string) ($_POST['empresa'] ?? ''));

    // candidato (procura emprego)
    $areaInteresse = trim((string) ($_POST['area_interesse'] ?? ''));
    $cidade = trim((string) ($_POST['cidade'] ?? ''));
    $resumo = trim((string) ($_POST['resumo'] ?? ''));
    $pretensao = trim((string) ($_POST['pretensao'] ?? ''));

    if (!csrfValido($_POST['csrf'] ?? '')) {
        $erro = 'Sessão expirada. Atualize a página e tente novamente.';
    } elseif ($nome === '') {
        $erro = 'Informe seu nome.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } elseif (strlen($senha) < 8) {
        $erro = 'A senha deve ter pelo menos 8 caracteres.';
    } elseif ($senha !== $confirmarSenha) {
        $erro = 'As senhas não conferem.';
    } elseif ($tipo === 'trabalhador' && ($especialidade === '' || $area === '')) {
        $erro = 'Informe a especialidade e a área de atendimento.';
    } elseif ($tipo === 'candidato' && $areaInteresse === '') {
        $erro = 'Informe a área ou profissão que você procura.';
    } elseif (
        $tipo === 'candidato'
        && $pretensao !== ''
        && !Candidato::valorValido($pretensao)
    ) {
        $erro = 'Informe a pretensão salarial só com números (ex.: 2500,00).';
    } elseif ($tipo === 'contratante' && $empresa === '') {
        $erro = 'Informe o nome da empresa.';
    } else {
        $pdo = Conexao::obter();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('
                INSERT INTO usuario
                    (nome, email, telefone, senha)
                VALUES (?, ?, ?, ?)
            ');
            $stmt->execute([
                $nome,
                $email,
                $telefone,
                password_hash($senha, PASSWORD_DEFAULT),
            ]);

            $idUsuario = (int) $pdo->lastInsertId();

            if ($tipo === 'trabalhador') {
                $stmt = $pdo->prepare('
                    INSERT INTO trabalhador
                        (id_trabalhador, cpf, especialidade, area_atendimento, descricao_perfil)
                    VALUES (?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $idUsuario,
                    $cpf !== '' ? $cpf : null,
                    $especialidade,
                    $area,
                    $descricao !== '' ? $descricao : null,
                ]);
            } elseif ($tipo === 'candidato') {
                $stmt = $pdo->prepare('
                    INSERT INTO candidato
                        (id_candidato, area_interesse, cidade, resumo, pretensao_salarial)
                    VALUES (?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $idUsuario,
                    $areaInteresse,
                    $cidade !== '' ? $cidade : null,
                    $resumo !== '' ? $resumo : null,
                    Candidato::converterValor($pretensao),
                ]);
            } else {
                $stmt = $pdo->prepare('
                    INSERT INTO contratante
                        (id_contratante, cnpj, empresa)
                    VALUES (?, ?, ?)
                ');
                $stmt->execute([
                    $idUsuario,
                    $cnpj !== '' ? $cnpj : null,
                    $empresa,
                ]);
            }

            $pdo->commit();
            header('Location: login.php?cadastro=ok');
            exit;
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erro = $ex->getCode() === '23000'
                ? 'Este e-mail já está cadastrado.'
                : 'Não foi possível concluir o cadastro.';
        }
    }
}

$titulo = 'Cadastro – LocalWorks';
require_once __DIR__ . '/../includes/cabecalho.php';
?>

<section class="secao">
    <div class="container" style="max-width: 42rem;">
        <h1 class="h2 fw-bold mb-4">Criar conta</h1>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="post" class="cartao p-4">
            <?= csrfCampo() ?>
            <input type="hidden" name="tipo" value="<?= e($tipo) ?>">

            <div class="mb-3">
                <label class="form-label" for="tipo">Como você quer usar o LocalWorks?</label>
                <select class="form-select" id="tipo" onchange="alterarTipo(this.value)">
                    <option value="candidato" <?= $tipo === 'candidato' ? 'selected' : '' ?>>
                        Procuro emprego ou vaga
                    </option>
                    <option value="trabalhador" <?= $tipo === 'trabalhador' ? 'selected' : '' ?>>
                        Ofereço meus serviços (autônomo / prestador)
                    </option>
                    <option value="contratante" <?= $tipo === 'contratante' ? 'selected' : '' ?>>
                        Quero contratar (empresa ou pessoa)
                    </option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label" for="nome">Nome</label>
                <input class="form-control" id="nome" name="nome" value="<?= e($nome) ?>" maxlength="100" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">E-mail</label>
                <input class="form-control" type="email" id="email" name="email" value="<?= e($email) ?>" maxlength="100" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="telefone">Telefone</label>
                <input class="form-control" id="telefone" name="telefone" value="<?= e($telefone) ?>" maxlength="20">
            </div>

            <!-- Candidato: procura emprego -->
            <div id="campos-candidato">
                <div class="mb-3">
                    <label class="form-label" for="area_interesse">Profissão ou área que você procura</label>
                    <input class="form-control" id="area_interesse" name="area_interesse" value="<?= e($areaInteresse) ?>" maxlength="100" placeholder="Ex.: Eletricista, Desenvolvedor, Auxiliar administrativo">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="cidade">Cidade onde procura trabalho</label>
                    <input class="form-control" id="cidade" name="cidade" value="<?= e($cidade) ?>" maxlength="100" placeholder="Ex.: Campinas SP">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="pretensao">Pretensão salarial (R$, opcional)</label>
                    <input class="form-control" id="pretensao" name="pretensao" value="<?= e($pretensao) ?>" maxlength="12" placeholder="Ex.: 2500,00">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="resumo">Resumo profissional</label>
                    <textarea class="form-control" id="resumo" name="resumo" rows="4" maxlength="1000"><?= e($resumo) ?></textarea>
                </div>
            </div>

            <!-- Trabalhador: presta serviços -->
            <div id="campos-trabalhador">
                <div class="mb-3">
                    <label class="form-label" for="cpf">CPF</label>
                    <input class="form-control" id="cpf" name="cpf" value="<?= e($cpf) ?>" maxlength="14">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="especialidade">Especialidade</label>
                    <input class="form-control" id="especialidade" name="especialidade" value="<?= e($especialidade) ?>" maxlength="100">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="area">Área de atendimento</label>
                    <input class="form-control" id="area" name="area" value="<?= e($area) ?>" maxlength="100">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="descricao">Apresentação</label>
                    <textarea class="form-control" id="descricao" name="descricao" rows="4"><?= e($descricao) ?></textarea>
                </div>
            </div>

            <!-- Contratante -->
            <div id="campos-contratante">
                <div class="mb-3">
                    <label class="form-label" for="cnpj">CNPJ</label>
                    <input class="form-control" id="cnpj" name="cnpj" value="<?= e($cnpj) ?>" maxlength="18">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="empresa">Empresa</label>
                    <input class="form-control" id="empresa" name="empresa" value="<?= e($empresa) ?>" maxlength="100">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="senha">Senha</label>
                <input class="form-control" type="password" id="senha" name="senha" minlength="8" required>
            </div>

            <div class="mb-4">
                <label class="form-label" for="confirmar_senha">Confirmar senha</label>
                <input class="form-control" type="password" id="confirmar_senha" name="confirmar_senha" minlength="8" required>
            </div>

            <button class="btn btn-primary w-100" type="submit">Criar conta</button>
        </form>
    </div>
</section>

<script>
function alterarTipo(tipo) {
    var blocos = {
        candidato: 'campos-candidato',
        trabalhador: 'campos-trabalhador',
        contratante: 'campos-contratante'
    };
    for (var chave in blocos) {
        document.getElementById(blocos[chave]).style.display =
            chave === tipo ? 'block' : 'none';
    }
    document.querySelector('input[name="tipo"]').value = tipo;
}
alterarTipo('<?= e($tipo) ?>');
</script>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
