# LocalWorks

**Plataforma Digital de Apoio ao Trabalho Local e à Geração de Renda**

Projeto Técnico de Conclusão de Curso (PTCC) — Curso Técnico em Desenvolvimento de Sistemas.

O LocalWorks é um sistema web que aproxima, em uma mesma região, **trabalhadores e prestadores de serviço**, **pessoas em busca de emprego** e **contratantes** (moradores e pequenos negócios). O projeto está alinhado ao **ODS 8 da ONU** (Trabalho Decente e Crescimento Econômico).

---

## Funcionalidades

- Cadastro e login com controle de sessão e de perfil de acesso
- Quatro perfis: **Trabalhador**, **Contratante**, **Candidato** e **Administrador**
- Publicação de oportunidades com data de publicação e **salário opcional**
- Seção **Propostas recentes** na página inicial
- Busca de profissionais por categoria e localização
- Perfil profissional com histórico de serviços realizados
- Candidatura a oportunidades (botão **Candidatar-me**), com acompanhamento pelo candidato e consulta dos candidatos pelo contratante
- Sugestão de novas categorias, com aprovação pelo administrador
- Painéis por perfil e relatórios de uso para o administrador

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Interface | HTML5, CSS3, JavaScript e Bootstrap 5 |
| Lógica | PHP orientado a objetos |
| Banco de dados | MySQL (banco `LocalWorks_DB`) com acesso via PDO |
| Ambiente local | XAMPP (Apache + MySQL) |
| Apoio ao projeto | VS Code, Git/GitHub e Trello (Scrum) |

## Estrutura do projeto

```
LocalWorks/
├── database/
│   ├── LocalWorks.sql              # cria o banco LocalWorks_DB e as tabelas
│   └── migracao_candidato.sql      # acrescenta o perfil Candidato (candidato e candidatura)
├── includes/
│   ├── iniciar.php                 # inicialização comum (sessão e classes)
│   ├── cabecalho.php               # topo e menu
│   ├── rodape.php                  # rodapé
│   └── cartao_proposta.php         # cartão de proposta reutilizável
├── public/                         # páginas acessadas pelo navegador
│   ├── assets/estilo.css
│   ├── index.php                   # página inicial e Propostas recentes
│   ├── cadastro.php  login.php  sair.php  criar_admin.php
│   ├── buscar.php  perfil.php
│   ├── oportunidades.php  oportunidade_detalhe.php
│   ├── painel.php  painel_candidato.php
│   ├── minhas_candidaturas.php  candidatos.php
│   └── sugerir_categoria.php
└── src/                            # classes PHP
    ├── Conexao.php  Autenticacao.php  Profissionais.php
    ├── Oportunidades.php  Painel.php
    └── Candidato.php  Candidatura.php
```

> Ajuste os nomes das pastas acima caso a organização do seu repositório seja diferente.

## Como executar (XAMPP)

**Pré-requisito:** [XAMPP](https://www.apachefriends.org/) instalado (PHP e MySQL).

1. Inicie os módulos **Apache** e **MySQL** no painel do XAMPP.
2. Copie a pasta do projeto `LocalWorks` para o diretório `htdocs` do XAMPP.
3. Abra o **phpMyAdmin** (`http://localhost/phpmyadmin`) e importe `database/LocalWorks.sql`. Ele cria o banco `LocalWorks_DB`.
4. Execute `database/migracao_candidato.sql` para criar as estruturas do perfil Candidato (se o banco já as contiver, esta etapa não é necessária).
5. Confirme os dados de conexão (servidor, usuário e senha do MySQL) na classe `src/Conexao.php`.
6. Acesse a pasta `public` pelo navegador, por exemplo:
   `http://localhost/LocalWorks/public/`
   (se o Apache estiver em outra porta, como a 8081 usada nos testes da equipe: `http://localhost:8081/LocalWorks/public/`).

### Criando o administrador inicial

Abra `criar_admin.php` pelo navegador **uma única vez** para criar a conta de administrador. Em seguida, **remova ou bloqueie esse arquivo**.

## Perfis e uso básico

| Perfil | O que faz |
|---|---|
| Trabalhador | Monta o perfil profissional, registra serviços realizados e é encontrado nas buscas |
| Contratante | Publica oportunidades, busca profissionais e vê os candidatos às suas vagas |
| Candidato | Consulta oportunidades, usa *Candidatar-me* e acompanha suas candidaturas |
| Administrador | Acompanha o uso da plataforma, consulta relatórios e aprova categorias |

## Requisitos do projeto

Atendidos: RF01 a RF08 e RF11 (cadastros, login, oportunidades, busca por categoria e localização, perfil, serviços realizados e relatórios).
Evolução futura: RF09 (edição de dados cadastrais) e RF10 (recuperação de senha).

## Segurança — antes de publicar na internet

- Remover ou bloquear `criar_admin.php`
- Armazenar senhas com `password_hash()` / `password_verify()`
- Escapar dados exibidos com `htmlspecialchars()`
- Usar um usuário MySQL próprio, com senha, no lugar do `root`
- Usar HTTPS e manter backups do banco

## Fora do escopo desta fase

Aplicativo móvel nativo, chat em tempo real, integração com sistemas bancários ou governamentais e recomendações por inteligência artificial.

## Equipe

| Nome | Função |
|---|---|
| Ademir Santos Neves | Scrum Master |
| Elias Cáceres Marra | Front-End |
| Isaías Paulino da Silva | Back-End |
| Sidiney Aparecido da Silva Barcelos | Product Owner |

## Licença

Projeto de caráter acadêmico e experimental, sem fins comerciais.
