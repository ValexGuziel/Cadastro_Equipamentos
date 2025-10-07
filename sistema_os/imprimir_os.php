<?php
require_once 'api/db_connect.php';

$os_id = (int)($_GET['id'] ?? 0);
$os = null;

if ($os_id > 0) {
    $sql = "
        SELECT 
            os.*,
            eq.nome as equipamento_nome,
            eq.tag as equipamento_tag,
            s.nome as setor_nome,
            tm.nome as tipo_manutencao_nome
        FROM ordens_servico os
        LEFT JOIN equipamentos eq ON os.equipamento_id = eq.id
        LEFT JOIN setores s ON os.setor_id = s.id
        LEFT JOIN tipos_manutencao tm ON os.tipo_manutencao_id = tm.id
        WHERE os.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $os_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $os = $result->fetch_assoc();
    $stmt->close();
}

if (!$os) {
    die("Ordem de Serviço não encontrada!");
}

function formatarData($data) {
    if (empty($data)) return 'N/A';
    try {
        $dt = new DateTime($data);
        return $dt->format('d/m/Y H:i');
    } catch (Exception $e) {
        return 'Data inválida';
    }
}

function formatarMoeda($valor) {
    $valor_float = (float)($valor ?? 0.0);
    return 'R$ ' . number_format($valor_float, 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Ordem de Serviço - <?= htmlspecialchars($os['numero_os']) ?></title>
<style>
    /* Reset */
    * {
        box-sizing: border-box;
    }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f0f2f5;
        margin: 0;
        padding: 20px;
        color: #2c3e50;
    }
    .print-btn {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #007bff;
        color: white;
        border: none;
        padding: 12px 20px;
        font-size: 16px;
        border-radius: 6px;
        cursor: pointer;
        box-shadow: 0 4px 8px rgb(0 123 255 / 0.4);
        transition: background-color 0.3s ease;
        z-index: 1000;
    }
    .print-btn:hover {
        background: #0056b3;
    }
    .container {
        max-width: 900px;
        margin: 80px auto 40px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgb(0 0 0 / 0.1);
        padding: 30px 40px;
    }
    header {
        border-bottom: 3px solid #007bff;
        padding-bottom: 15px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 20px;
    }
    header h1 {
        font-size: 32px;
        font-weight: 700;
        color: #007bff;
        margin: 0;
        flex-grow: 1;
    }
    header .os-number {
        font-size: 20px;
        font-weight: 600;
        color: #34495e;
        background: #e7f1ff;
        padding: 8px 16px;
        border-radius: 8px;
        box-shadow: inset 0 0 6px rgb(0 123 255 / 0.2);
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit,minmax(250px,1fr));
        gap: 25px 40px;
        margin-bottom: 40px;
    }
    .info-card {
        background: #f7f9fc;
        border-radius: 10px;
        padding: 20px 25px;
        box-shadow: 0 4px 12px rgb(0 0 0 / 0.05);
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: box-shadow 0.3s ease;
    }
    .info-card:hover {
        box-shadow: 0 8px 20px rgb(0 0 0 / 0.12);
    }
    .info-card strong {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .info-card span {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        word-break: break-word;
    }
    .description-section {
        background: #f7f9fc;
        border-radius: 12px;
        padding: 25px 30px;
        box-shadow: 0 6px 20px rgb(0 0 0 / 0.07);
    }
    .description-section strong {
        display: block;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 15px;
        color: #007bff;
        text-transform: uppercase;
        letter-spacing: 0.07em;
    }
    .description-section p {
        font-size: 16px;
        line-height: 1.6;
        color: #34495e;
        white-space: pre-wrap;
    }
    footer {
        margin-top: 50px;
        text-align: center;
        font-size: 13px;
        color: #95a5a6;
        font-style: italic;
        user-select: none;
    }
    @media print {
        body {
            background: #fff;
            padding: 0;
            margin: 0;
        }
        .print-btn {
            display: none;
        }
        .container {
            box-shadow: none;
            border-radius: 0;
            padding: 0;
            margin: 0;
            max-width: 100%;
        }
        header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .info-card {
            box-shadow: none;
            padding: 10px 15px;
        }
        .description-section {
            box-shadow: none;
            padding: 15px 20px;
        }
        footer {
            margin-top: 30px;
            font-size: 11px;
        }
    }
</style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Imprimir</button>

    <div class="container" role="main" aria-label="Ordem de Serviço <?= htmlspecialchars($os['numero_os']) ?>">
        <header>
            <h1>Ordem de Serviço</h1>
            <div class="os-number" aria-label="Número da Ordem de Serviço"><?= htmlspecialchars($os['numero_os']) ?></div>
        </header>

        <section class="info-grid" aria-label="Informações principais da ordem de serviço">
            <article class="info-card" aria-labelledby="equipamento-label">
                <strong id="equipamento-label">Equipamento</strong>
                <span><?= htmlspecialchars($os['equipamento_tag'] . ' - ' . $os['equipamento_nome']) ?></span>
            </article>
            <article class="info-card" aria-labelledby="setor-label">
                <strong id="setor-label">Setor</strong>
                <span><?= htmlspecialchars($os['setor_nome']) ?></span>
            </article>
            <article class="info-card" aria-labelledby="tipo-manutencao-label">
                <strong id="tipo-manutencao-label">Tipo de Manutenção</strong>
                <span><?= htmlspecialchars($os['tipo_manutencao_nome']) ?></span>
            </article>
            <article class="info-card" aria-labelledby="area-manutencao-label">
                <strong id="area-manutencao-label">Área de Manutenção</strong>
                <span><?= htmlspecialchars($os['area_manutencao']) ?></span>
            </article>
            <article class="info-card" aria-labelledby="prioridade-label">
                <strong id="prioridade-label">Prioridade</strong>
                <span><?= htmlspecialchars($os['prioridade']) ?></span>
            </article>
            <article class="info-card" aria-labelledby="status-label">
                <strong id="status-label">Status</strong>
                <span><?= htmlspecialchars($os['status']) ?></span>
            </article>
            <article class="info-card" aria-labelledby="solicitante-label">
                <strong id="solicitante-label">Solicitante</strong>
                <span><?= htmlspecialchars($os['solicitante']) ?></span>
            </article>
            <article class="info-card" aria-labelledby="data-inicial-label">
                <strong id="data-inicial-label">Data Inicial</strong>
                <span><?= formatarData($os['data_inicial']) ?></span>
            </article>
            <article class="info-card" aria-labelledby="data-final-label">
                <strong id="data-final-label">Data Final</strong>
                <span><?= formatarData($os['data_final']) ?></span>
            </article>
            <article class="info-card" aria-labelledby="custo-pecas-label">
                <strong id="custo-pecas-label">Custo Peças</strong>
                <span><?= formatarMoeda($os['custo_pecas']) ?></span>
            </article>
            <article class="info-card" aria-labelledby="custo-mo-label">
                <strong id="custo-mo-label">Custo Mão de Obra</strong>
                <span><?= formatarMoeda($os['custo_mao_de_obra']) ?></span>
            </article>
            <article class="info-card" aria-labelledby="custo-total-label" style="background-color: #e7f1ff;">
                <strong id="custo-total-label">Custo Total</strong>
                <span style="font-weight: 700; color: #0056b3;">
                    <?= formatarMoeda(($os['custo_pecas'] ?? 0) + ($os['custo_mao_de_obra'] ?? 0)) ?>
                </span>
            </article>
        </section>

        <section class="description-section" aria-labelledby="descricao-label">
            <strong id="descricao-label">Descrição do Problema</strong>
            <p><?= nl2br(htmlspecialchars($os['descricao_problema'])) ?></p>
        </section>

        <footer>
            &copy; <?= date('Y') ?> Sua Empresa - Impresso em <?= date('d/m/Y H:i') ?>
        </footer>
    </div>
</body>
</html>
