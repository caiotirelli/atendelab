<?php

class PessoasController
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

        $sql  = 'SELECT id, nome, documento, telefone, email, curso, periodo, status, criado_em
                 FROM pessoas
                 ORDER BY nome ASC';
        $stmt = $this->pdo->query($sql);

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

        $stmt = $this->pdo->prepare('SELECT * FROM pessoas WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pessoa = $stmt->fetch();

        if (!$pessoa) {
            http_response_code(404);
            echo json_encode(['erro' => 'Pessoa nao encontrada.']);
            return;
        }

        echo json_encode($pessoa, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $nome      = trim($_POST['nome']      ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $email     = trim($_POST['email']     ?? '');
        $telefone  = trim($_POST['telefone']  ?? '');
        $curso     = trim($_POST['curso']     ?? '');
        $periodo   = trim($_POST['periodo']   ?? '');
        $observacoes = trim($_POST['observacoes'] ?? '');
        $status    = $_POST['status']         ?? 'ativo';

        if ($nome === '' || $documento === '' || $email === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome, documento e e-mail sao obrigatorios.']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'E-mail invalido.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status invalido.']);
            return;
        }

        try {
            $sql = 'INSERT INTO pessoas (nome, documento, telefone, email, curso, periodo, observacoes, status)
                    VALUES (:nome, :documento, :telefone, :email, :curso, :periodo, :observacoes, :status)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome',        $nome);
            $stmt->bindValue(':documento',   $documento);
            $stmt->bindValue(':telefone',    $telefone);
            $stmt->bindValue(':email',       $email);
            $stmt->bindValue(':curso',       $curso);
            $stmt->bindValue(':periodo',     $periodo);
            $stmt->bindValue(':observacoes', $observacoes);
            $stmt->bindValue(':status',      $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Pessoa cadastrada com sucesso.',
                'id'       => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar pessoa. Documento pode estar duplicado.']);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $nome      = trim($_POST['nome']      ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $email     = trim($_POST['email']     ?? '');
        $telefone  = trim($_POST['telefone']  ?? '');
        $curso     = trim($_POST['curso']     ?? '');
        $periodo   = trim($_POST['periodo']   ?? '');
        $observacoes = trim($_POST['observacoes'] ?? '');
        $status    = $_POST['status']         ?? 'ativo';

        if (!$id || $nome === '' || $documento === '' || $email === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID, nome, documento e e-mail sao obrigatorios.']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'E-mail invalido.']);
            return;
        }

        try {
            $sql = 'UPDATE pessoas SET
                        nome        = :nome,
                        documento   = :documento,
                        telefone    = :telefone,
                        email       = :email,
                        curso       = :curso,
                        periodo     = :periodo,
                        observacoes = :observacoes,
                        status      = :status
                    WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome',        $nome);
            $stmt->bindValue(':documento',   $documento);
            $stmt->bindValue(':telefone',    $telefone);
            $stmt->bindValue(':email',       $email);
            $stmt->bindValue(':curso',       $curso);
            $stmt->bindValue(':periodo',     $periodo);
            $stmt->bindValue(':observacoes', $observacoes);
            $stmt->bindValue(':status',      $status);
            $stmt->bindValue(':id',          $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Pessoa atualizada com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar pessoa.']);
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
            $stmt = $this->pdo->prepare('UPDATE pessoas SET status = :status WHERE id = :id');
            $stmt->bindValue(':status', 'inativo');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Pessoa inativada com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao inativar pessoa.']);
        }
    }
}