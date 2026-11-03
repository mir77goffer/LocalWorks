<?php
declare(strict_types=1);

require_once __DIR__ . '/Conexao.php';

final class Candidatura
{
    private static function rodar(string $sql, array $params = []): PDOStatement
    {
        $stmt = Conexao::obter()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // ---------- Rótulos de status (usados nas telas) ----------

    public static function rotulo(string $status): string
    {
        return match ($status) {
            'aceita' => 'Aceita',
            'recusada' => 'Recusada',
            default => 'Em análise',
        };
    }

    public static function classe(string $status): string
    {
        return match ($status) {
            'aceita' => 'text-bg-success',
            'recusada' => 'text-bg-secondary',
            default => 'text-bg-warning',
        };
    }

    // ---------- Lado do candidato / trabalhador ----------

    /**
     * Retorna 'ok', 'duplicada' ou 'indisponivel'.
     */
    public static function candidatar(
        int $idOportunidade,
        int $idUsuario,
        string $mensagem
    ): string {
        $existe = self::rodar(
            'SELECT 1 FROM oportunidade
             WHERE id_oportunidade = ? AND publicada = 1',
            [$idOportunidade]
        )->fetchColumn();

        if (!$existe) {
            return 'indisponivel';
        }

        try {
            self::rodar(
                'INSERT INTO candidatura (id_oportunidade, id_usuario, mensagem)
                 VALUES (?, ?, ?)',
                [$idOportunidade, $idUsuario, $mensagem !== '' ? $mensagem : null]
            );
            return 'ok';
        } catch (PDOException $ex) {
            // 23000 = violação de UNIQUE (já se candidatou)
            if ($ex->getCode() === '23000') {
                return 'duplicada';
            }
            throw $ex;
        }
    }

    public static function daOportunidade(int $idOportunidade, int $idUsuario): ?array
    {
        return self::rodar(
            'SELECT id_candidatura, status, mensagem, data_candidatura
             FROM candidatura
             WHERE id_oportunidade = ? AND id_usuario = ?',
            [$idOportunidade, $idUsuario]
        )->fetch() ?: null;
    }

    public static function retirar(int $idCandidatura, int $idUsuario): bool
    {
        $stmt = self::rodar(
            'DELETE FROM candidatura
             WHERE id_candidatura = ? AND id_usuario = ?',
            [$idCandidatura, $idUsuario]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Candidaturas de um usuário. O contato da empresa só vem preenchido
     * quando a candidatura foi aceita (a regra fica no SQL, não na tela).
     */
    public static function minhas(int $idUsuario): array
    {
        return self::rodar(
            "SELECT
                ca.id_candidatura,
                ca.status,
                ca.data_candidatura,
                o.id_oportunidade,
                o.titulo,
                o.localizacao,
                o.publicada,
                ct.empresa,
                CASE WHEN ca.status = 'aceita' THEN u.email END AS contato_email,
                CASE WHEN ca.status = 'aceita' THEN u.telefone END AS contato_telefone
             FROM candidatura ca
             INNER JOIN oportunidade o
                ON o.id_oportunidade = ca.id_oportunidade
             INNER JOIN contratante ct
                ON ct.id_contratante = o.id_contratante
             INNER JOIN usuario u
                ON u.id_usuario = ct.id_contratante
             WHERE ca.id_usuario = ?
             ORDER BY ca.data_candidatura DESC, ca.id_candidatura DESC",
            [$idUsuario]
        )->fetchAll();
    }

    // ---------- Lado do contratante ----------

    /**
     * Quantidade de candidaturas por oportunidade: [id_oportunidade => total].
     */
    public static function contagemPorOportunidade(int $idContratante): array
    {
        $linhas = self::rodar(
            'SELECT o.id_oportunidade, COUNT(ca.id_candidatura) AS total
             FROM oportunidade o
             LEFT JOIN candidatura ca
                ON ca.id_oportunidade = o.id_oportunidade
             WHERE o.id_contratante = ?
             GROUP BY o.id_oportunidade',
            [$idContratante]
        )->fetchAll();

        return array_column($linhas, 'total', 'id_oportunidade');
    }

    public static function oportunidadeDoContratante(
        int $idOportunidade,
        int $idContratante
    ): ?array {
        return self::rodar(
            'SELECT id_oportunidade, titulo, localizacao, publicada
             FROM oportunidade
             WHERE id_oportunidade = ? AND id_contratante = ?',
            [$idOportunidade, $idContratante]
        )->fetch() ?: null;
    }

    /**
     * Candidatos de uma oportunidade do contratante. E-mail e telefone do
     * candidato só aparecem depois que a candidatura é aceita.
     */
    public static function candidatos(int $idOportunidade, int $idContratante): array
    {
        return self::rodar(
            "SELECT
                ca.id_candidatura,
                ca.mensagem,
                ca.status,
                ca.data_candidatura,
                u.id_usuario,
                u.nome,
                t.id_trabalhador IS NOT NULL AS eh_trabalhador,
                COALESCE(c.area_interesse, t.especialidade) AS area,
                COALESCE(c.cidade, t.area_atendimento) AS cidade,
                COALESCE(c.resumo, t.descricao_perfil) AS resumo,
                c.pretensao_salarial,
                CASE WHEN ca.status = 'aceita' THEN u.email END AS email,
                CASE WHEN ca.status = 'aceita' THEN u.telefone END AS telefone
             FROM candidatura ca
             INNER JOIN oportunidade o
                ON o.id_oportunidade = ca.id_oportunidade
             INNER JOIN usuario u
                ON u.id_usuario = ca.id_usuario
             LEFT JOIN candidato c
                ON c.id_candidato = u.id_usuario
             LEFT JOIN trabalhador t
                ON t.id_trabalhador = u.id_usuario
             WHERE ca.id_oportunidade = ?
               AND o.id_contratante = ?
             ORDER BY ca.data_candidatura DESC, ca.id_candidatura DESC",
            [$idOportunidade, $idContratante]
        )->fetchAll();
    }

    /**
     * Aceita, recusa ou volta para análise. Só funciona se a candidatura
     * for de uma oportunidade do próprio contratante.
     */
    public static function definirStatus(
        int $idCandidatura,
        int $idContratante,
        string $status
    ): bool {
        if (!in_array($status, ['enviada', 'aceita', 'recusada'], true)) {
            return false;
        }

        $dono = self::rodar(
            'SELECT 1
             FROM candidatura ca
             INNER JOIN oportunidade o
                ON o.id_oportunidade = ca.id_oportunidade
             WHERE ca.id_candidatura = ? AND o.id_contratante = ?',
            [$idCandidatura, $idContratante]
        )->fetchColumn();

        if (!$dono) {
            return false;
        }

        self::rodar(
            'UPDATE candidatura SET status = ? WHERE id_candidatura = ?',
            [$status, $idCandidatura]
        );
        return true;
    }
}
