// script.js
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const dataFinalInput = document.getElementById('data_final');
    const formOS = document.getElementById('form-os');
    const equipamentoSelect = document.getElementById('equipamento_id');
    const setorSelect = document.getElementById('setor_id');

    // Lógica para habilitar/desabilitar a Data Final
    statusSelect.addEventListener('change', function() {
        if (this.value === 'Concluída') {
            dataFinalInput.disabled = false;
            dataFinalInput.required = true;
        } else {
            dataFinalInput.disabled = true;
            dataFinalInput.required = false;
            dataFinalInput.value = ''; // Limpa o valor se não for 'Concluída'
        }
    });

    // Lógica para auto-selecionar o setor ao escolher um equipamento
    equipamentoSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const setorId = selectedOption.getAttribute('data-setor-id');
        if (setorId) {
            setorSelect.value = setorId;
        }
    });

    // Função genérica para atualizar selects
    async function atualizarSelect(selectId, endpoint, novoValorId, isEquipamento = false) {
        try {
            const response = await fetch(endpoint);
            const options = await response.json();
            
            const select = document.getElementById(selectId);
            const currentValue = select.value; // Salva o valor atual se houver

            select.innerHTML = '<option value="" selected disabled>Selecione...</option>'; // Limpa e adiciona o placeholder

            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.id;
                optionElement.textContent = option.nome;
                select.appendChild(optionElement);

                // Adiciona atributos extras para o select de equipamento
                if (isEquipamento) {
                    optionElement.textContent = `${option.tag} - ${option.nome}`;
                    optionElement.setAttribute('data-setor-id', option.setor_id);
                }
            });

            // Seleciona o novo valor adicionado
            if (novoValorId) {
                select.value = novoValorId;
            } else if (currentValue) {
                select.value = currentValue; // Restaura o valor anterior se não for um novo item
            }


        } catch (error) {
            console.error('Erro ao atualizar select:', error);
            alert('Não foi possível carregar as novas opções.');
        }
    }

    // Salvar novo Setor (via modal)
    document.getElementById('btn-salvar-setor').addEventListener('click', async function() {
        const form = document.getElementById('form-add-setor');
        const formData = new FormData(form);

        if (!form.checkValidity()) {
            alert('Por favor, preencha o nome do setor.');
            return;
        }

        try {
            const response = await fetch('api/add_setor.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                // Fecha o modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalSetor'));
                modal.hide();
                form.reset();
                // Atualiza o select de setores e seleciona o novo
                await atualizarSelect('setor_id', 'api/get_options.php?tipo=setores', result.id);
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            console.error('Erro ao salvar setor:', error);
            alert('Ocorreu um erro de comunicação.');
        }
    });

    // Salvar novo Tipo de Manutenção (via modal)
    document.getElementById('btn-salvar-tipo').addEventListener('click', async function() {
        const form = document.getElementById('form-add-tipo');
        const formData = new FormData(form);

        if (!form.checkValidity()) {
            alert('Por favor, preencha o nome do tipo de manutenção.');
            return;
        }

        try {
            const response = await fetch('api/add_tipo.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalTipoManutencao'));
                modal.hide();
                form.reset();
                await atualizarSelect('tipo_manutencao_id', 'api/get_options.php?tipo=tipos_manutencao', result.id);
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            console.error('Erro ao salvar tipo:', error);
            alert('Ocorreu um erro de comunicação.');
        }
    });

    // Carregar setores no modal de equipamento quando ele for aberto
    const modalEquipamento = document.getElementById('modalEquipamento');
    modalEquipamento.addEventListener('show.bs.modal', function () {
        atualizarSelect('novo_equip_setor', 'api/get_options.php?tipo=setores');
    });

    // Salvar novo Equipamento (via modal)
    document.getElementById('btn-salvar-equipamento').addEventListener('click', async function() {
        const form = document.getElementById('form-add-equipamento');
        const formData = new FormData(form);
        const messageDiv = document.getElementById('equipamento-modal-message');

        // Reseta a mensagem a cada tentativa
        messageDiv.style.display = 'none';
        messageDiv.className = 'alert';

        if (!form.checkValidity()) {
            messageDiv.textContent = 'Por favor, preencha todos os campos do equipamento.';
            messageDiv.classList.add('alert-warning');
            messageDiv.style.display = 'block';
            return;
        }

        try {
            const response = await fetch('api/add_equipamento.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                messageDiv.textContent = 'Equipamento cadastrado com sucesso!';
                messageDiv.classList.add('alert-success');
                messageDiv.style.display = 'block';

                form.reset();
                // Atualiza o select de equipamentos e seleciona o novo
                await atualizarSelect('equipamento_id', 'api/get_options.php?tipo=equipamentos', result.id, true);
                
                // Dispara o evento 'change' para auto-selecionar o setor
                equipamentoSelect.dispatchEvent(new Event('change'));

                // Fecha o modal após 2 segundos
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(modalEquipamento);
                    modal.hide();
                }, 2000);
            } else {
                messageDiv.textContent = 'Erro: ' + result.message;
                messageDiv.classList.add('alert-danger');
                messageDiv.style.display = 'block';
            }
        } catch (error) {
            console.error('Erro ao salvar equipamento:', error);
            alert('Ocorreu um erro de comunicação.');
        }
    });



    // Enviar o formulário principal da O.S.
    formOS.addEventListener('submit', async function(event) {
        event.preventDefault(); // Impede o envio padrão do formulário

        if (!this.checkValidity()) {
            this.classList.add('was-validated'); // Mostra feedback de validação do Bootstrap
            return;
        }

        const formData = new FormData(this);
        
        try {
            const response = await fetch('api/salvar_os.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                // Esconde o formulário e mostra a mensagem de sucesso
                formOS.style.display = 'none';
                const successDiv = document.getElementById('success-message');
                document.getElementById('success-os-number').textContent = result.numero_os;
                document.getElementById('btn-imprimir-os').href = `imprimir_os.php?id=${result.id}`;
                successDiv.style.display = 'block';

                document.getElementById('btn-nova-os').addEventListener('click', () => {
                    window.location.reload();
                });
            } else {
                alert('Erro ao salvar a O.S.: ' + result.message);
            }
        } catch (error) {
            console.error('Erro ao enviar formulário:', error);
            alert('Ocorreu um erro de comunicação ao salvar a O.S.');
        }
    });
});
