<?php

class TiposAtendimentosController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $stmt = $this->pdo->query('SELECT * FROM tipos_atendimentos ORDER BY nome ASC');
        echo json_encode($stmt->fetchAll(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function buscar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID invalido.']);
            return;
        }

        $stmt = $this->pdo->prepare('SELECT * FROM tipos_atendimentos WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $tipo = $stmt->fetch();

        if (!$tipo) {
            http_response_code(404);
            echo json_encode(['erro' => 'Tipo nao encontrado.']);
            return;
        }

        echo json_encode($tipo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $nome      = trim($_POST['nome']      ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $status    = $_POST['status']         ?? 'ativo';

        if ($nome === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome e obrigatorio.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status invalido.']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO tipos_atendimentos (nome, descricao, status) VALUES (:nome, :descricao, :status)'
            );
            $stmt->bindValue(':nome',      $nome);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':status',    $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Tipo cadastrado com sucesso.',
                'id'       => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar tipo.']);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $nome      = trim($_POST['nome']      ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $status    = $_POST['status']         ?? 'ativo';

        if (!$id || $nome === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID e nome sao obrigatorios.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status invalido.']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE tipos_atendimentos SET nome = :nome, descricao = :descricao, status = :status WHERE id = :id'
            );
            $stmt->bindValue(':nome',      $nome);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':status',    $status);
            $stmt->bindValue(':id',        $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Tipo atualizado com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar tipo.']);
        }
    }

    public function inativar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID invalido.']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare('UPDATE tipos_atendimentos SET status = :status WHERE id = :id');
            $stmt->bindValue(':status', 'inativo');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Tipo inativado com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao inativar tipo.']);
        }
    }
}