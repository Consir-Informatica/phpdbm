<?php

namespace Consir\Phpdbm;

use PDO;
use PDOException;

class DBM
{
    private $host = "localhost";
    private $database = "gmail-api";
    private $user = "root";
    private $pass = "";

    /**
     * Conexão com a base de dados  
     * @var PDO 
     */
    private $pdo = null;

    private $tabelPrefix = "gapi";

    /**
     * Conecta com o banco de dados
     * @return void
     * @throws PDOException
     */
    function __construct()
    {
        $this->pdo = new PDO(
            "mysql:host=" . $_ENV['DATABASE_HOST'] . ";dbname=" . $_ENV['DATABASE_NAME'],
            $_ENV['DATABASE_USER'],
            $_ENV['DATABASE_PASS'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    }

    /**
     * Realiza um select com campos customizados e where simpes de 1 campo
     * @param string $table Nome da tabela
     * @param array $campos Campos retornados da tabela
     * @param array $where Cláusula WHERE
     * @param boolean $enforce_array força o reotorno a ser encapsulado em um array
     * @return mixed
     * @throws PDOException
     */
    function select($table, $campos = [], $where = [], $enforce_array = false)
    {
        if (empty($table)) {
            return false;
        } else {
            $pdo = $this->pdo;

            // Prepara query
            $sql = "SELECT ";

            if (empty($campos)) {
                $sql .= "* ";
            } else {
                foreach ($campos as $campo) {
                    $sql .= $campo . ", ";
                }
                $sql = rtrim($sql, ", ");
                $sql .= " ";
            }

            $sql .= "FROM " . $this->tabelPrefix . "_" . $table . " ";



            // Executa query

            if (!empty($where)) {
                $key = array_keys($where);
                $value = array_values($where);

                $sql .= " WHERE " . $key[0] . " = :" . $key[0];
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":" . $key[0], $value[0]);
            } else {
                $stmt = $pdo->prepare($sql);
            }
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($result) === 1 && !$enforce_array) {
                return $result[0];
            }

            return $result;
        }
    }

    /**
     * Insere dados no banco de dados
     * @param string $table Nome da tabela
     * @param array $data Par chave e valor, a serem inseridos na tabela
     * @return boolean True se sucesso, False se erro
     * @throws PDOException
     */
    public function insert($table, $data)
    {
        if (empty($data) || empty($table)) {
            return false;
        } else {
            $pdo = $this->pdo;

            // Prepara query
            $sql = "INSERT INTO " . $this->tabelPrefix . "_" . $table . " (";

            foreach ($data as $key => $value) {
                $sql .= $key . ", ";
            }

            $sql = rtrim($sql, ", ");
            $sql .= ") VALUES (";

            foreach ($data as $key => $value) {
                $sql .= ":" . $key . ", ";
            }
            $sql = rtrim($sql, ", ");
            $sql .= ")";
        }

        // Executa query

        $stmt = $pdo->prepare($sql);

        foreach ($data as $key => $value) {
            switch (gettype($value)) {
                case "integer":
                    $type = PDO::PARAM_INT;
                    break;
                default:
                    $type = PDO::PARAM_STR;
                    break;
            }

            $stmt->bindValue(":" . $key, $value, $type);
        }

        return $stmt->execute();
    }

    /**
     * Atualiza registros
     * @param sting $table nome da tabela
     * @param array $data dados para serem atualizados
     * @param array $where cláusula WHERE
     * @return boolean sucesso
     * @throws PDOException
     */
    public function update($table, $data = [], $where = [])
    {
        if (empty($data) || empty($table) || empty($where)) {
            return false;
        } else {
            $pdo = $this->pdo;

            // Prepara query
            $sql = "UPDATE " . $this->tabelPrefix . "_" . $table . " SET ";

            foreach ($data as $key => $value) {
                $sql .= $key . " = " . ":" . $key . ", ";
            }

            $sql = rtrim($sql, ", ");
        }

        // Executa query

        $where_key = array_keys($where);
        $where_value = array_values($where);

        $sql .= " WHERE " . $where_key[0] . " = :" . $where_key[0];

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":" . $where_key[0], $where_value[0]);

        foreach ($data as $key => $value) {
            switch (gettype($value)) {
                case "integer":
                    $type = PDO::PARAM_INT;
                    break;
                default:
                    $type = PDO::PARAM_STR;
                    break;
            }

            $stmt->bindValue(":" . $key, $value, $type);
        }

        return $stmt->execute();
    }
}
