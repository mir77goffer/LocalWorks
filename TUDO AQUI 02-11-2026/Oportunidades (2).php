<?php
declare(strict_types=1);

require_once __DIR__ . '/Conexao.php';

final class Oportunidades
{
    public static function buscar(int $idCategoria, string $local): array
    {
        $sql = '
            SELECT
                o.id_oportunidade,
                o.titulo,
                o.descricao,
                o.localizacao,
                o.valor_proposto,
                o.data_publicacao,
                c.nome AS categoria,
                ct.empresa
            FROM oportunidade o
            INNER JOIN categoria c
                ON c.id_categoria = o.id_categoria
            INNER JOIN contratante ct
                ON ct.id_contratante = o.id_contratante
            WHERE o.publicada = 1
        ';
        $params = [];

        if ($idCategoria > 0) {
            $sql .= ' AND o.id_categoria = ?';
            $params[] = $idCategoria;
        }

        if ($local !== '') {
            $sql .= ' AND o.localizacao LIKE ?';
            $params[] = '%' . self::escaparLike($local) . '%';
        }

        $sql .= ' ORDER BY o.id_oportunidade DESC LIMIT 50';

        $stmt = Conexao::obter()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Últimas oportunidades publicadas (usado em "Propostas recentes" na home).
     */
    public static function recentes(int $limite = 3): array
    {
        $limite = max(1, min($limite, 12));

        $sql = '
            SELECT
                o.id_oportunidade,
                o.titulo,
                o.descricao,
                o.localizacao,
                o.valor_proposto,
                o.data_publicacao,
                c.nome AS categoria,
                ct.empresa
            FROM oportunidade o
            INNER JOIN categoria c
                ON c.id_categoria = o.id_categoria
            INNER JOIN contratante ct
                ON ct.id_contratante = o.id_contratante
            WHERE o.publicada = 1
            ORDER BY o.data_publicacao DESC, o.id_oportunidade DESC
            LIMIT ' . $limite;

        return Conexao::obter()->query($sql)->fetchAll();
    }

    public static function contar(): int
    {
        return (int) Conexao::obter()
            ->query('SELECT COUNT(*) FROM oportunidade WHERE publicada = 1')
            ->fetchColumn();
    }

    public static function buscarPorId(int $id): ?array
    {
        $stmt = Conexao::obter()->prepare(
            'SELECT
                o.id_oportunidade,
                o.id_contratante,
                o.titulo,
                o.descricao,
                o.localizacao,
                o.valor_proposto,
                o.data_publicacao,
                c.nome AS categoria,
                ct.empresa,
                u.nome AS contratante,
                u.email,
                u.telefone
             FROM oportunidade o
             INNER JOIN categoria c
                ON c.id_categoria = o.id_categoria
             INNER JOIN contratante ct
                ON ct.id_contratante = o.id_contratante
             INNER JOIN usuario u
                ON u.id_usuario = ct.id_contratante
             WHERE o.id_oportunidade = ?
               AND o.publicada = 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    private static function escaparLike(string $texto): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $texto);
    }
}
