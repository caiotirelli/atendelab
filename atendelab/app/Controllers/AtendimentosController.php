<?php

class AtendimentosController
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

        $sql = 'SELECT
                    a.id,
                    p.nome AS pessoa,
                    t.nome AS tipo,
                    u.nome AS responsavel,
                    a.descricao,
                    a.status,
                    a.data_atendimento,
                    a.horario_atendimento,
                    a.observacao_final,
                    a.criado_em
                FROM atendimentos a
                JOIN pessoas p ON p.id = a.pessoa_id
                JOIN tipos_atendimentos t ON t.id = a.tipo_atendimento_id
                JOIN usuarios u ON u.id = a.usuario_id
                ORDER BY a.id DESC';

        $stmt = $this->pdo->query($sql);
        $atendimentos = $stmt->fetchAll();

        foreach ($atendimentos as &$atendimento) {
            $atendimento['protocolo'] = 'ATD-' . str_pad((string)$atendimento['id'], 4, '0', STR_PAD_LEFT);
        }

        echo json_encode($atendimentos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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

        $sql = 'SELECT
                    a.*,
                    p.nome AS pessoa,
                    t.nome AS tipo,
                    u.nome AS responsavel
                FROM atendimentos a
                JOIN pessoas p ON p.id = a.pessoa_id
                JOIN tipos_atendimentos t ON t.id = a.tipo_atendimento_id
                JOIN usuarios u ON u.id = a.usuario_id
                WHERE a.id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $atendimento = $stmt->fetch();

        if (!$atendimento) {
            http_response_code(404);
            echo json_encode(['erro' => 'Atendimento nao encontrado.']);
            return;
        }

        $atendimento['protocolo'] = 'ATD-' . str_pad((string)$atendimento['id'], 4, '0', STR_PAD_LEFT);

        echo json_encode($atendimento, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $pessoa_id            = isset($_POST['pessoa_id'])            ? (int)$_POST['pessoa_id']            : 0;
        $tipo_atendimento_id  = isset($_POST['tipo_atendimento_id'])  ? (int)$_POST['tipo_atendimento_id']  : 0;
        $usuario_id           = (int)(usuarioAtual()['id'] ?? 0);
        $descricao            = trim($_POST['descricao']              ?? '');
        $data_atendimento     = trim($_POST['data_atendimento']       ?? '');
        $horario_atendimento  = trim($_POST['horario_atendimento']    ?? '');
        $status               = $_POST['status']                      ?? 'aberto';

        if (!$pessoa_id || !$tipo_atendimento_id || !$usuario_id || $descricao === '' || $data_atendimento === '' || $horario_atendimento === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Todos os campos obrigatorios devem ser preenchidos.']);
            return;
        }

        if (!in_array($status, ['aberto', 'em_andamento', 'concluido'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status invalido.']);
            return;
        }

        try {
            $sql = 'INSERT INTO atendimentos
                        (pessoa_id, tipo_atendimento_id, usuario_id, descricao, status, data_atendimento, horario_atendimento)
                    VALUES
                        (:pessoa_id, :tipo_atendimento_id, :usuario_id, :descricao, :status, :data_atendimento, :horario_atendimento)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':pessoa_id',           $pessoa_id,           PDO::PARAM_INT);
            $stmt->bindValue(':tipo_atendimento_id', $tipo_atendimento_id, PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id',          $usuario_id,          PDO::PARAM_INT);
            $stmt->bindValue(':descricao',           $descricao);
            $stmt->bindValue(':status',              $status);
            $stmt->bindValue(':data_atendimento',    $data_atendimento);
            $stmt->bindValue(':horario_atendimento', $horario_atendimento);
            $stmt->execute();

            $id = $this->pdo->lastInsertId();

            http_response_code(201);
            echo json_encode([
                'mensagem'  => 'Atendimento registrado com sucesso.',
                'id'        => $id,
                'protocolo' => 'ATD-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT)
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao registrar atendimento. Verifique os IDs informados.']);
        }
    }

    public function alterarStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id                = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $status            = trim($_POST['status'] ?? '');
        $observacao_final  = trim($_POST['observacao_final'] ?? '');

        if (!$id || $status === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID e status sao obrigatorios.']);
            return;
        }

        if (!in_array($status, ['aberto', 'em_andamento', 'concluido'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status invalido.']);
            return;
        }

        if ($status === 'concluido' && $observacao_final === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Observacao final e obrigatoria para concluir o atendimento.']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE atendimentos SET status = :status, observacao_final = :observacao_final WHERE id = :id'
            );
            $stmt->bindValue(':status',           $status);
            $stmt->bindValue(':observacao_final', $observacao_final);
            $stmt->bindValue(':id',               $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Status atualizado com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar status.']);
        }
    }
}