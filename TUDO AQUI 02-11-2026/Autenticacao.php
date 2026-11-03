<?php
declare(strict_types=1);

require_once __DIR__ . '/Conexao.php';

final class Autenticacao
{
    public static function entrar(string $email, string $senha): ?array
    {
        $sql = '
            SELECT
                u.id_usuario,
                u.nome,
                u.senha,
                t.id_trabalhador IS NOT NULL AS eh_trabalhador,
                c.id_candidato IS NOT NULL AS eh_candidato,
                a.id_admin IS NOT NULL AS eh_admin
            FROM usuario u
            LEFT JOIN trabalhador t
                ON t.id_trabalhador = u.id_usuario
            LEFT JOIN candidato c
                ON c.id_candidato = u.id_usuario
            LEFT JOIN administrador a
                ON a.id_admin = u.id_usuario
            WHERE u.email = ?
        ';

        $stmt = Conexao::obter()->prepare($sql);
        $stmt->execute([$email]);
        $linha = $stmt->fetch();

        if (!$linha || !password_verify($senha, $linha['senha'])) {
            return null;
        }

        if ($linha['eh_admin']) {
            $tipo = 'administrador';
        } elseif ($linha['eh_trabalhador']) {
            $tipo = 'trabalhador';
        } elseif ($linha['eh_candidato']) {
            $tipo = 'candidato';
        } else {
            $tipo = 'contratante';
        }

        return [
            'id' => (int) $linha['id_usuario'],
            'nome' => $linha['nome'],
            'tipo' => $tipo,
        ];
    }
}
