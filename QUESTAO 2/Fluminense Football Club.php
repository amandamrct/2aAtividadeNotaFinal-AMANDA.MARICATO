<?php
$databasePath = __DIR__ . '/data/fluminense.db';
$pdo = new PDO('sqlite:' . $databasePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("
    CREATE TABLE IF NOT EXISTS jogos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        adversario TEXT NOT NULL,
        data_jogo TEXT NOT NULL,
        local TEXT NOT NULL,
        placar TEXT DEFAULT ''
    )
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['adicionar_jogo'])) {
        $stmt = $pdo->prepare("INSERT INTO jogos (adversario, data_jogo, local) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['adversario'], $_POST['data_jogo'], $_POST['local']]);
    }

    if (isset($_POST['atualizar_placar'])) {
        $stmt = $pdo->prepare("UPDATE jogos SET placar = ? WHERE id = ?");
        $stmt->execute([$_POST['placar'], $_POST['id']]);
    }

    if (isset($_POST['excluir_jogo'])) {
        $stmt = $pdo->prepare("DELETE FROM jogos WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

$jogos = $pdo->query("SELECT * FROM jogos ORDER BY date(data_jogo)")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Fluminense - Gerenciador de Jogos</title>
    <style>
        :root {
            --granate: #7F0000;
            --verde: #009A44;
            --branco: #FFFFFF;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .jogo-card {
            background: var(--branco);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 5px solid var(--granate);
            position: relative;
        }
        .jogo-header {
            margin-bottom: 10px;
        }
        .jogo-title {
            font-size: 1.3em;
            color: var(--granate);
            margin: 0;
        }
        .jogo-info {
            color: #555;
            margin-bottom: 10px;
        }
        .placar-container {
            margin-top: 15px;
        }
        .placar-view {
            display: inline-block;
            font-weight: bold;
        }
        .placar-form {
            display: none;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        .placar-input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100px;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-editar {
            background: var(--verde);
            color: white;
            margin-left: 10px;
        }
        .btn-salvar {
            background: var(--verde);
            color: white;
        }
        .btn-excluir {
            background: #d9534f;
            color: white;
        }
        .acoes {
            position: absolute;
            top: 15px;
            right: 15px;
        }
        .form-adicionar {
            background: rgba(127, 0, 0, 0.1);
            border: 1px solid #7F0000;
            padding: 15px;
            border-radius: 7px;               
            margin-bottom: 30px;
            }
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
    </style>
    <script>
        function editarPlacar(jogoId) {
            document.getElementById('placar-view-' + jogoId).style.display = 'none';
            document.getElementById('btn-editar-' + jogoId).style.display = 'none';
            document.getElementById('placar-form-' + jogoId).style.display = 'flex';
        }
    </script>
</head>
<body>
    <div class="container">
        <h1> Jogos do Fluminense</h1>

        <div class="form-adicionar">
            <h3>Adicionar Novo Jogo</h3>
            <form method="post">
                <div class="form-group">
                    <label>Adversário:</label>
                    <input type="text" name="adversario" required>
                </div>
                <div class="form-group">
                    <label>Data do Jogo:</label>
                    <input type="date" name="data_jogo" required>
                </div>
                <div class="form-group">
                    <label>Local:</label>
                    <input type="text" name="local" required>
                </div>
                <button type="submit" name="adicionar_jogo" class="btn btn-salvar">
                    Adicionar Jogo
                </button>
            </form>
        </div>

        <?php foreach ($jogos as $jogo): ?>
            <div class="jogo-card">
                <div class="acoes">
                    <button type="button" id="btn-editar-<?= $jogo['id'] ?>" class="btn btn-editar" 
                            onclick="editarPlacar(<?= $jogo['id'] ?>)">
                        Editar
                    </button>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $jogo['id'] ?>">
                        <button type="submit" name="excluir_jogo" class="btn btn-excluir"
                                onclick="return confirm('Tem certeza que deseja excluir este jogo?')">
                            Excluir
                        </button>
                    </form>
                </div>

                <div class="jogo-header">
                    <h3 class="jogo-title">Fluminense vs <?= htmlspecialchars($jogo['adversario']) ?></h3>
                </div>

                <div class="jogo-info">
                    <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($jogo['data_jogo'])) ?></p>
                    <p><strong>Local:</strong> <?= htmlspecialchars($jogo['local']) ?></p>
                </div>

                <div class="placar-container">
                    <div id="placar-view-<?= $jogo['id'] ?>" class="placar-view">
                        <?php if (!empty($jogo['placar'])): ?>
                            <strong>Placar:</strong> <?= htmlspecialchars($jogo['placar']) ?>
                        <?php else: ?>
                            <em>Placar não informado</em>
                        <?php endif; ?>
                    </div>

                    <form id="placar-form-<?= $jogo['id'] ?>" method="post" class="placar-form">
                        <input type="hidden" name="id" value="<?= $jogo['id'] ?>">
                        <label for="placar">Placar:</label>
                        <input type="text" name="placar" class="placar-input" 
                               value="<?= htmlspecialchars($jogo['placar']) ?>" 
                               placeholder="Ex: 2x1">
                        <button type="submit" name="atualizar_placar" class="btn btn-salvar">
                            Salvar
                        </button>
                        <button type="button" class="btn btn-excluir" 
                                onclick="document.getElementById('placar-form-<?= $jogo['id'] ?>').style.display='none'; document.getElementById('placar-view-<?= $jogo['id'] ?>').style.display='inline-block'; document.getElementById('btn-editar-<?= $jogo['id'] ?>').style.display='inline-block'">
                            Cancelar
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>