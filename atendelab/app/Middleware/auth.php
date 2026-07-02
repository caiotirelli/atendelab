<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function usuarioAutenticado(): bool
{
    return isset($_SESSION['usuario'])
        && is_array($_SESSION['usuario']);
}

function exigirAutenticacao(): void
{
    if (!usuarioAutenticado()) {
        $_SESSION['mensagem'] = 'Faca login para acessar a area restrita.';
        header('Location: ?controller=auth&action=login');
        exit;
    }
}

function usuarioAtual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function usuarioEhAdmin(): bool
{
    $usuario = usuarioAtual();
    return $usuario !== null && ($usuario['perfil'] ?? '') === 'admin';
}

function exigirAdmin(): void
{
    exigirAutenticacao();

    if (!usuarioEhAdmin()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode(['erro' => 'Apenas administradores podem realizar esta acao.']);
        exit;
    }
}