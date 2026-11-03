<?php
declare(strict_types=1);

require_once __DIR__ . '/Conexao.php';

final class Profissionais
{
    public static function listarCategorias(): array
    {
        return Conexao::obter()
            ->query(
                'SELECT id_categoria, nome
                 FROM categoria
                 WHERE aprovada = 1
                 ORDER BY nome'
            )
            ->fetchAll();
    }

    public static function buscar(int $idCategoria, string $local): array
    {
        $sql = '
            SELECT
                u.id_usuario,
                u.nome,
                t.especialidade,
                t.area_atendimento,
                t.descricao_perfil
            FROM usuario u
            INNER JOIN trabalhador t
                ON t.id_trabalhador = u.id_usuario
            WHERE 1 = 1
        ';
        $params = [];

        if ($idCategoria > 0) {
            $sql .= '
                AND (
                    t.especialidade = (
                        SELECT nome
                        FROM categoria
                        WHERE id_categoria = ?
                    )
                    OR EXISTS (
                        SELECT 1
                        FROM servico s
                        WHERE s.id_trabalhador = t.id_trabalhador
                          AND s.id_categoria = ?
                    )
                )
            ';
            $params[] = $idCategoria;
            $params[] = $idCategoria;
        }

        if ($local !== '') {
            $sql .= ' AND t.area_atendimento LIKE ?';
            $params[] = '%' . self::escaparLike($local) . '%';
        }

        $sql .= ' ORDER BY u.nome LIMIT 50';

        $stmt = Conexao::obter()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function buscarPorId(int $id): ?array
    {
        $stmt = Conexao::obter()->prepare(
            'SELECT
                u.id_usuario,
                u.nome,
                u.email,
                u.telefone,
                t.especialidade,
                t.area_atendimento,
                t.descricao_perfil
             FROM usuario u
             INNER JOIN trabalhador t
                ON t.id_trabalhador = u.id_usuario
             WHERE u.id_usuario = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function listarServicos(int $idTrabalhador): array
    {
        $stmt = Conexao::obter()->prepare(
            'SELECT
                s.id_servico,
                s.descricao,
                s.valor,
                s.data_servico,
                c.nome AS categoria
             FROM servico s
             INNER JOIN categoria c
                ON c.id_categoria = s.id_categoria
             WHERE s.id_trabalhador = ?
             ORDER BY s.data_servico DESC, s.id_servico DESC'
        );
        $stmt->execute([$idTrabalhador]);
        return $stmt->fetchAll();
    }

    public static function salvarPerfil(
        int $idTrabalhador,
        string $area,
        string $descricao
    ): void {
        $stmt = Conexao::obter()->prepare(
            'UPDATE trabalhador
             SET area_atendimento = ?,
                 descricao_perfil = ?
             WHERE id_trabalhador = ?'
        );
        $stmt->execute([$area, $descricao, $idTrabalhador]);
    }

    private static function escaparLike(string $texto): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $texto);
    }
}
