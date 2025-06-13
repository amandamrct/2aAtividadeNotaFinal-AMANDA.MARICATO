<?php
// Conexão com o banco SQLite
$db = new PDO('sqlite:livraria.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Criar tabela se não existir
$db->exec("CREATE TABLE IF NOT EXISTS livros (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo TEXT NOT NULL,
    autor TEXT NOT NULL,
    ano_publicacao INTEGER NOT NULL
)");

// Processar adição de livro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'])) {
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $ano = $_POST['ano'];
    
    $stmt = $db->prepare("INSERT INTO livros (titulo, autor, ano_publicacao) VALUES (?, ?, ?)");
    $stmt->execute([$titulo, $autor, $ano]);
}

// Processar exclusão de livro
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $db->prepare("DELETE FROM livros WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: index.php');
    exit;
}

// Obter todos os livros
$livros = $db->query("SELECT * FROM livros ORDER BY titulo")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Livraria Trovão e Baleias</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #2c3e50;
            color: white;
            border-radius: 5px;
        }
        h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #2c3e50;
            color: white;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            color: #7f8c8d;
        }
        .btn-excluir {
            color: #e74c3c;
            text-decoration: none;
            font-weight: bold;
        }
        .modal-exclusao {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-exclusao-conteudo {
            background-color: white;
            margin: 200px auto;
            padding: 25px;
            width: 300px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            text-align: center;
        }
        .modal-exclusao-botoes {
            margin-top: 25px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .modal-exclusao-btn {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .modal-exclusao-confirmar {
            background-color: #e74c3c;
            color: white;
        }
        .modal-exclusao-cancelar {
            background-color: #95a5a6;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Livraria Trovão e Baleias</h1>
    </div>
    
    <h2>Adicionar Livro</h2>
    <form method="post">
        <div class="form-group">
            <label for="titulo">Timbre:</label>
            <input type="text" id="titulo" name="titulo" required>
        </div>
        
        <div class="form-group">
            <label for="autor">Autor:</label>
            <input type="text" id="autor" name="autor" required>
        </div>
        
        <div class="form-group">
            <label for="ano">Ano de Publicação:</label>
            <input type="number" id="ano" name="ano" required min="1000" max="<?= date('Y') ?>">
        </div>
        
        <button type="submit">Adicionar Livro</button>
    </form>
    
    <h2>Livros Cadastrados</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Timbre</th>
                <th>Autor</th>
                <th>Ano</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($livros as $livro): ?>
            <tr>
                <td><?= htmlspecialchars($livro['id']) ?></td>
                <td><?= htmlspecialchars($livro['titulo']) ?></td>
                <td><?= htmlspecialchars($livro['autor']) ?></td>
                <td><?= htmlspecialchars($livro['ano_publicacao']) ?></td>
                <td>
                    <a href="#" onclick="abrirModalExclusao(<?= $livro['id'] ?>); return false;" class="btn-excluir">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <p>Livraria Trovão e Baleias © <?= date('Y') ?></p>
    </div>

    <!-- Modal de Exclusão Personalizado -->
    <div id="modalExclusao" class="modal-exclusao">
        <div class="modal-exclusao-conteudo">
            <p>Confirme a exclusão do livro</p>
            <div class="modal-exclusao-botoes">
                <button id="btnConfirmarExclusao" class="modal-exclusao-btn modal-exclusao-confirmar">OK</button>
                <button id="btnCancelarExclusao" class="modal-exclusao-btn modal-exclusao-cancelar">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        // Variável para armazenar o ID do livro a excluir
        let livroIdExclusao = null;
        
        // Função para abrir o modal
        function abrirModalExclusao(id) {
            livroIdExclusao = id;
            document.getElementById('modalExclusao').style.display = 'block';
        }
        
        // Configurar eventos dos botões
        document.getElementById('btnConfirmarExclusao').addEventListener('click', function() {
            if (livroIdExclusao) {
                window.location.href = '?delete_id=' + livroIdExclusao;
            }
        });
        
        document.getElementById('btnCancelarExclusao').addEventListener('click', function() {
            document.getElementById('modalExclusao').style.display = 'none';
        });
        
        // Fechar modal ao clicar fora
        window.addEventListener('click', function(event) {
            if (event.target === document.getElementById('modalExclusao')) {
                document.getElementById('modalExclusao').style.display = 'none';
            }
        });
    </script>
</body>
</html>