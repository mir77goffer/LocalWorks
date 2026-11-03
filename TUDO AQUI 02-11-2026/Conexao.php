<?php
declare(strict_types=1);

final class Conexao
{
    private static ?PDO $pdo = null;

    public static function obter(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = '127.0.0.1';
        $porta = '3306';
        $banco = 'LocalWorks_DB';
        $usuario = 'root';
        $senha = '';

        $dsn = "mysql:host={$host};port={$porta};dbname={$banco};charset=utf8mb4";

        self::$pdo = new PDO($dsn, $usuario, $senha, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$pdo;
    }
}
