// script.js

document.addEventListener('DOMContentLoaded', function () {
    // --- LÓGICA PARA O FORMULÁRIO DE NOVA O.S. (index.php) ---
    const formOS = document.getElementById('form-os');
    if (formOS) {
        const equipamentoSelect = document.getElementById('equipamento_id');
        const setorSelect = document.getElementById('setor_id');

        // Atualiza o setor automaticamente quando um equipamento é selecionado
        equipamentoSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const setorId = selectedOption.dataset.setorId;
            if (setorId) {
                setorSelect.value = setorId;
            }
        });

        // Envio do formulário principal da O.S.
        formOS.addEventListener('submit', function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (!formOS.checkValidity()) {
                formOS.classList.add('was-validated');
                return;
            }

            const formData = new FormData(formOS);
            const url = 'api/salvar_os.php';

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    formOS.style.display = 'none';
                    const successDiv = document.getElementById('success-message');
                    successDiv.style.display = 'block';
                    document.getElementById('success-os-number').textContent = data.numero_os;
                    document.getElementById('btn-imprimir-os').href = `imprimir_os.php?id=${data.os_id}`;
                } else {
                    alert('Erro ao salvar: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Ocorreu um erro de comunicação ao tentar salvar a O.S.');
            });
        });

        // Botão para criar uma nova O.S. após o sucesso
        const btnNovaOS = document.getElementById('btn-nova-os');
        if (btnNovaOS) {
            btnNovaOS.addEventListener('click', function() {
                window.location.reload();
            });
        }
    }

    // --- LÓGICA PARA OS MODAIS DE CADASTRO RÁPIDO (usado em index.php) ---

    // Função genérica para adicionar opções a um <select>
    function adicionarOpcao(selectElement, valor, texto, dataset = {}) {
        const option = document.createElement('option');
        option.value = valor;
        option.textContent = texto;
        for (const key in dataset) {
            option.dataset[key] = dataset[key];
        }
        selectElement.appendChild(option);
        selectElement.value = valor; // Seleciona a nova opção
    }

    // Modal de Equipamento
    const modalEquipamento = document.getElementById('modalEquipamento');
    if (modalEquipamento) {
        const btnSalvarEquipamento = document.getElementById('btn-salvar-equipamento');
        const formAddEquipamento = document.getElementById('form-add-equipamento');
        
        // Carrega setores no modal de equipamento
        fetch('api/get_options.php?tipo=setores')
            .then(res => res.json())
            .then(setores => {
                const selectSetorModal = document.getElementById('novo_equip_setor');
                setores.forEach(setor => adicionarOpcao(selectSetorModal, setor.id, setor.nome));
            });

        btnSalvarEquipamento.addEventListener('click', function() {
            const formData = new FormData(formAddEquipamento);
            fetch('api/add_equipamento.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(result => {
                    const msgDiv = document.getElementById('equipamento-modal-message');
                    msgDiv.textContent = result.message;
                    msgDiv.className = result.success ? 'alert alert-success' : 'alert alert-danger';
                    msgDiv.style.display = 'block';

                    if (result.success) {
                        const equipamentoSelect = document.getElementById('equipamento_id');
                        const setorId = formData.get('setor_id');
                        const nomeCompleto = `${formData.get('tag')} - ${formData.get('nome')}`;
                        adicionarOpcao(equipamentoSelect, result.id, nomeCompleto, { setorId: setorId });
                        equipamentoSelect.dispatchEvent(new Event('change')); // Dispara o evento para atualizar o setor no form principal
                        setTimeout(() => bootstrap.Modal.getInstance(modalEquipamento).hide(), 1500);
                    }
                });
        });
    }

    // Função genérica para salvar itens simples (Setor, Tipo de Manutenção)
    function setupModalSave(modalId, formId, buttonId, apiUrl, selectToUpdateId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const form = document.getElementById(formId);
        const button = document.getElementById(buttonId);

        button.addEventListener('click', function() {
            const formData = new FormData(form);
            const nome = formData.get('nome');
            if (!nome) {
                alert('O nome não pode ser vazio.');
                return;
            }

            fetch(apiUrl, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        const mainSelect = document.getElementById(selectToUpdateId);
                        adicionarOpcao(mainSelect, result.id, nome);
                        bootstrap.Modal.getInstance(modal).hide();
                    } else {
                        alert('Erro: ' + result.message);
                    }
                });
        });
    }

    // Configura os modais de Setor e Tipo
    setupModalSave('modalSetor', 'form-add-setor', 'btn-salvar-setor', 'api/add_setor.php', 'setor_id');
    setupModalSave('modalTipoManutencao', 'form-add-tipo', 'btn-salvar-tipo', 'api/add_tipo.php', 'tipo_manutencao_id');
});