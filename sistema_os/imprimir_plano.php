<?php
require_once 'api/db_connect.php';

$plano_id = (int)($_GET['id'] ?? 0);
$plano = null;

if ($plano_id > 0) {
    $sql = "
        SELECT 
            pm.id,
            pm.periodicidade,
            pm.data_ultima_preventiva,
            pm.data_proxima_preventiva,
            pm.instrucoes,
            eq.nome as equipamento_nome,
            eq.tag as equipamento_tag,
            s.nome as setor_nome
        FROM planos_manutencao pm
        JOIN equipamentos eq ON pm.equipamento_id = eq.id
        JOIN setores s ON eq.setor_id = s.id
        WHERE pm.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $plano_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $plano = $result->fetch_assoc();
    $stmt->close();
}

if (!$plano) {
    die("Plano de Manutenção não encontrado!");
}

function formatarData($data) {
    if (empty($data)) return 'N/A';
    try {
        $dt = new DateTime($data);
        return $dt->format('d/m/Y');
    } catch (Exception $e) {
        return 'Data inválida';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Plano de Manutenção - Equip. <?= htmlspecialchars($plano['equipamento_tag']) ?></title>
<style>
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
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.4);
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
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        padding: 30px 40px;
    }
    header {
        border-bottom: 3px solid #007bff;
        padding-bottom: 15px;
        margin-bottom: 30px;
    }
    header h1 {
        font-size: 32px;
        font-weight: 700;
        color: #007bff;
        margin: 0;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }
    .info-card strong {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 8px;
        text-transform: uppercase;
    }
    .info-card span {
        font-size: 18px;
        font-weight: 600;
    }
    .description-section strong {
        display: block;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 15px;
        color: #007bff;
    }
    .description-section p {
        font-size: 16px;
        line-height: 1.6;
        white-space: pre-wrap;
        background: #f7f9fc;
        padding: 20px;
        border-radius: 8px;
    }
    @media print {
        body { background: #fff; padding: 0; margin: 0; }
        .print-btn { display: none; }
        .container { box-shadow: none; border-radius: 0; padding: 0; margin: 0; max-width: 100%; }
    }
</style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Imprimir Plano</button>

    <div class="container">
        <header><h1>Plano de Manutenção Preventiva</h1></header>

        <section class="info-grid">
            <div class="info-card"><strong>Equipamento</strong><span><?= htmlspecialchars($plano['equipamento_tag'] . ' - ' . $plano['equipamento_nome']) ?></span></div>
            <div class="info-card"><strong>Setor</strong><span><?= htmlspecialchars($plano['setor_nome']) ?></span></div>
            <div class="info-card"><strong>Periodicidade</strong><span><?= htmlspecialchars($plano['periodicidade']) ?></span></div>
            <div class="info-card"><strong>Última Preventiva</strong><span><?= formatarData($plano['data_ultima_preventiva']) ?></span></div>
            <div class="info-card"><strong>Próxima Preventiva</strong><span><?= formatarData($plano['data_proxima_preventiva']) ?></span></div>
        </section>

        <section class="description-section">
            <strong>Instruções de Manutenção (Checklist)</strong>
            <p><?= nl2br(htmlspecialchars($plano['instrucoes'])) ?></p>
        </section>
    </div>
</body>
</html>