<?php
$tituloPagina = 'Usuários';
require __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Usuários</h1>
        <p class="text-secondary mb-0">Gerenciamento de usuários do sistema.</p>
    </div>
    <button class="btn btn-success" type="button" onclick="novoUsuario()">Novo usuário</button>
</div>

<div id="alerta"></div>

<div class="card border-0 shadow-sm mb-4 d-none" id="cardFormulario">
    <div class="card-body">
        <h2 class="h5" id="tituloFormulario">Novo usuário</h2>
        <form id="formUsuario">
            <input type="hidden" name="id" id="usuarioId">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome *</label>
                    <input class="form-control" name="nome" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">E-mail *</label>
                    <input class="form-control" type="email" name="email" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Senha *</label>
                    <input class="form-control" type="password" name="senha" id="inputSenha">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Perfil</label>
                    <select class="form-select" name="perfil">
                        <option value="atendente">Atendente</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-success" type="submit">Salvar</button>
                <button class="btn btn-outline-secondary" type="button" onclick="fecharFormulario()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody id="tabelaUsuarios">
                <tr><td colspan="5" class="text-center py-4">Carregando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
const formUsuario = document.getElementById('formUsuario');
const cardFormulario = document.getElementById('cardFormulario');
const inputSenha = document.getElementById('inputSenha');

function abrirFormulario() {
    cardFormulario.classList.remove('d-none');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function fecharFormulario() {
    cardFormulario.classList.add('d-none');
    formUsuario.reset();
    document.getElementById('usuarioId').value = '';
    inputSenha.required = true;
    inputSenha.placeholder = '';
}

function novoUsuario() {
    fecharFormulario();
    document.getElementById('tituloFormulario').textContent = 'Novo usuário';
    inputSenha.required = true;
    abrirFormulario();
}

async function carregarUsuarios() {
    try {
        const dados = AtendeLabApi.toList(await AtendeLabApi.get('usuarios', 'listar'));
        const tbody = document.getElementById('tabelaUsuarios');
        if (!dados.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Nenhum usuário cadastrado.</td></tr>';
            return;
        }
        tbody.innerHTML = dados.map(u => `<tr>
            <td>${AtendeLabApi.escape(u.nome)}</td>
            <td>${AtendeLabApi.escape(u.email)}</td>
            <td><span class="badge text-bg-primary">${AtendeLabApi.escape(u.perfil)}</span></td>
            <td><span class="badge ${u.status === 'ativo' ? 'text-bg-success' : 'text-bg-secondary'}">${AtendeLabApi.escape(u.status)}</span></td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-primary" onclick="editarUsuario(${Number(u.id)})">Editar</button>
            </td>
        </tr>`).join('');
    } catch (error) {
        AtendeLabApi.showAlert('alerta', error.message, 'danger');
    }
}

async function editarUsuario(id) {
    try {
        const u = AtendeLabApi.toObject(await AtendeLabApi.get('usuarios', 'buscar', { id }));
        novoUsuario();
        document.getElementById('tituloFormulario').textContent = 'Editar usuário';
        inputSenha.required = false;
        inputSenha.placeholder = 'Deixe em branco para manter a senha atual';
        for (const [key, value] of Object.entries(u)) {
            const field = formUsuario.elements.namedItem(key);
            if (field && key !== 'senha') field.value = value ?? '';
        }
    } catch (error) {
        AtendeLabApi.showAlert('alerta', error.message, 'danger');
    }
}

formUsuario.addEventListener('submit', async event => {
    event.preventDefault();
    const id = document.getElementById('usuarioId').value;
    try {
        await AtendeLabApi.post('usuarios', id ? 'atualizar' : 'criar', new FormData(formUsuario));
        AtendeLabApi.showAlert('alerta', id ? 'Usuário atualizado com sucesso.' : 'Usuário cadastrado com sucesso.');
        fecharFormulario();
        await carregarUsuarios();
    } catch (error) {
        AtendeLabApi.showAlert('alerta', error.message, 'danger');
    }
});

document.addEventListener('DOMContentLoaded', carregarUsuarios);
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>