<?php
declare(strict_types=1);

require_once __DIR__ . '/Conexao.php';

final class Candidato
{
    // Aceita "2500", "2500.50" ou "2500,50". Vazio é tratado fora (opcional).
    public static function valorValido(string $texto): bool
    {
        $n = str_replace(',', '.', trim($texto));
        return is_numeric($n) && (float) $n >= 0 && (float) $n <= 99999999.99;
    }

    public static function converterValor(string $texto): ?float
    {
        $texto = trim($texto);
        if ($texto === '') {
            return null;
        }
        return (float) str_replace(',', '.', $texto);
    }

    public static function meusDados(int $id): ?array
    {
        $stmt = Conexao::obter()->prepare(
            'SELECT
                u.nome,
                u.telefone,
                c.area_interesse,
                c.cidade,
                c.resumo,
                c.pretensao_salarial
             FROM usuario u
             INNER JOIN candidato c
                ON c.id_candidato = u.id_usuario
             WHERE u.id_usuario = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function atualizarDados(int $id, array $d): void
    {
        $pdo = Conexao::obter();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'UPDATE usuario SET nome = ?, telefone = ? WHERE id_usuario = ?'
            );
            $stmt->execute([$d['nome'], $d['telefone'], $id]);

            $stmt = $pdo->prepare(
                'UPDATE candidato
                 SET area_interesse = ?,
                     cidade = ?,
                     resumo = ?,
                     pretensao_salarial = ?
                 WHERE id_candidato = ?'
            );
            $stmt->execute([
                $d['area_interesse'],
                $d['cidade'] !== '' ? $d['cidade'] : null,
                $d['resumo'] !== '' ? $d['resumo'] : null,
                $d['pretensao'],
                $id,
            ]);

            $pdo->commit();
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $ex;
        }
    }
}
