<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/iniciar.php';

$recentes = [];
$totalAbertas = 0;

try {
    $recentes = Oportunidades::recentes(3);
    $totalAbertas = Oportunidades::contar();
} catch (Throwable $ex) {
    error_log('index.php: ' . $ex->getMessage());
}

$titulo = 'LocalWorks – Plataforma de apoio ao trabalho local';
$paginaAtiva = 'inicio';
require_once __DIR__ . '/../includes/cabecalho.php';
?>

<section class="hero">
    <div class="container text-center text-lg-start">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <p class="text-uppercase fw-semibold small text-primary mb-2">
                    Trabalho decente · Economia local
                </p>
                <h1 class="display-4 mb-3">
                    Trabalho local, <span class="destaque">perto de você</span>.
                </h1>
                <p class="lead text-secondary mb-4">
                    Conectamos trabalhadores, prestadores de serviços e pequenos empreendedores
                    a oportunidades na sua região, de forma simples e segura.
                </p>
                <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 justify-content-center justify-content-lg-start">
                    <a href="cadastro.php?tipo=candidato" class="btn btn-primary btn-lg">
                        <i class="bi bi-person-vcard"></i> Procuro emprego
                    </a>
                    <a href="cadastro.php?tipo=trabalhador" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-tools"></i> Ofereço serviços
                    </a>
                    <a href="cadastro.php?tipo=contratante" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-briefcase"></i> Quero contratar
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($recentes): ?>
<section id="propostas" class="secao">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Propostas recentes</h2>
                <p class="text-secondary mb-0">
                    Empresas e pequenos negócios que acabaram de publicar oportunidades.
                </p>
            </div>
            <a class="fw-semibold" href="oportunidades.php">
                Ver todas (<?= (int) $totalAbertas ?>) <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php
            $hTag = 'h3';
            foreach ($recentes as $o) {
                include __DIR__ . '/../includes/cartao_proposta.php';
            }
            ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section id="como-funciona" class="secao">
    <div class="container">
        <h2 class="fw-bold text-center mb-5">Como funciona</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="cartao h-100 p-4 text-center">
                    <span class="icone mb-3"><i class="bi bi-person-plus"></i></span>
                    <h3 class="h5 fw-bold">1. Crie seu perfil</h3>
                    <p class="text-secondary mb-0">Cadastre-se como candidato, trabalhador ou contratante em poucos passos.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="cartao h-100 p-4 text-center">
                    <span class="icone mb-3"><i class="bi bi-search"></i></span>
                    <h3 class="h5 fw-bold">2. Encontre ou publique</h3>
                    <p class="text-secondary mb-0">Candidate-se a vagas, busque profissionais por categoria e localização ou publique uma oportunidade.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="cartao h-100 p-4 text-center">
                    <span class="icone mb-3"><i class="bi bi-patch-check"></i></span>
                    <h3 class="h5 fw-bold">3. Construa sua reputação</h3>
                    <p class="text-secondary mb-0">Registre os serviços realizados e monte seu histórico profissional.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="para-quem" class="secao secao-cinza">
    <div class="container">
        <h2 class="fw-bold text-center mb-5">Para quem é o LocalWorks</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="cartao bg-white h-100 p-4">
                    <span class="icone mb-3"><i class="bi bi-person-vcard"></i></span>
                    <h3 class="h5 fw-bold">Quem procura emprego</h3>
                    <p class="text-secondary mb-0">
                        Monte seu perfil, veja as vagas da sua região e candidate-se
                        com uma mensagem para a empresa.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="cartao bg-white h-100 p-4">
                    <span class="icone mb-3"><i class="bi bi-tools"></i></span>
                    <h3 class="h5 fw-bold">Trabalhadores e prestadores de serviços</h3>
                    <p class="text-secondary mb-0">
                        Divulgue suas habilidades, experiência e área de atendimento,
                        e seja encontrado por quem precisa do seu serviço.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="cartao bg-white h-100 p-4">
                    <span class="icone mb-3"><i class="bi bi-briefcase"></i></span>
                    <h3 class="h5 fw-bold">Contratantes e pequenos negócios</h3>
                    <p class="text-secondary mb-0">
                        Publique oportunidades, receba candidaturas e encontre profissionais
                        da sua região conferindo o perfil e o histórico de cada um.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="ods8" class="secao">
    <div class="container text-center">
        <span class="icone mb-3"><i class="bi bi-globe-americas"></i></span>
        <h2 class="fw-bold">Alinhado ao ODS 8</h2>
        <p class="lead text-secondary mx-auto" style="max-width: 46rem;">
            O LocalWorks é um projeto acadêmico ligado ao Objetivo de Desenvolvimento
            Sustentável 8, Trabalho Decente e Crescimento Econômico, da Agenda 2030 da ONU.
        </p>
    </div>
</section>

<section class="faixa-cta py-5">
    <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
        <h2 class="h3 fw-bold mb-0">Pronto para começar?</h2>
        <a href="cadastro.php" class="btn btn-lg">Criar minha conta</a>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
