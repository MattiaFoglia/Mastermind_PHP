<?php
session_start();

// Inizializza la combinazione segreta se non è già stata generata o se il bottone di reset è stato premuto
if (!isset($_SESSION['combinazione']) || isset($_POST['reset'])) {
    $_SESSION['combinazione'] = [rand(1, 4), rand(1, 4), rand(1, 4), rand(1, 4)];
    $_SESSION['attempts'] = 0;
    $_SESSION['history'] = []; // Svuota la cronologia dei tentativi
    $feedback = "<h1>Nuova partita iniziata!</h1>"; // Messaggio di conferma
}

// Mappatura colori con immagini
$colori = [
    1 => "./Images/rosso(1).gif",
    2 => "./Images/giallo.gif",
    3 => "./Images/blu.gif",
    4 => "./Images/verde.gif"
];

// Immagini per il feedback
$img_nero = "./Images/nero.gif";  // Posizione corretta
$img_bianco = "./Images/bianco(1).gif";  // Colore corretto, posizione sbagliata


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['colors'])) {
    $_SESSION['attempts']++;
    $user_guess = $_POST['colors'];
    $secret_code = $_SESSION['combinazione'];
    $black = 0;
    $white = 0;
    $code_copy = $secret_code;
    $user_guess_copy = $user_guess; // Copia per la visualizzazione

        // Controllo per i "neri" (colore e posizione corretta)
        for ($i = 0; $i < 4; $i++) {
            if ($user_guess[$i] == $code_copy[$i]) {
                $black++;
                $code_copy[$i] = null;
                $user_guess[$i] = null;
            }
        }

        // Controllo per i "bianchi" (colore giusto in posizione sbagliata)
        for ($i = 0; $i < 4; $i++) {
            if ($user_guess[$i] !== null) {
                for ($j = 0; $j < 4; $j++) {
                    if ($code_copy[$j] !== null && $user_guess[$i] == $code_copy[$j]) {
                        $white++;
                        $code_copy[$j] = null;
                        break;
                    }
                }
            }
        }

        // Aggiungi il tentativo alla cronologia
        $_SESSION['history'][] = [
            'colors' => $user_guess_copy, // Usa la copia originale per la visualizzazione
            'black' => $black,
            'white' => $white
        ];

        if ($black === 4) {
            $feedback = "<p>Hai indovinato la sequenza in " . $_SESSION['attempts'] . " tentativi!</p>";
            session_destroy(); // Termina la partita
        } elseif ($_SESSION['attempts'] >= $_SESSION['max_attempts']) {
            $feedback = "<p>Hai raggiunto il numero massimo di tentativi. La sequenza era: " . implode(", ", $_SESSION['combinazione']) . "</p>";
            session_destroy(); // Termina la partita
        } else {
            $feedback = "<p>Tentativo #" . $_SESSION['attempts'] . "</p>";
        }
    }

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mastermind</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Gioco del Mastermind</h1>
    <form method="post">
        <table>
            <tr>
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <td>
                        <select name="colors[]" required>
                            <option value="">Seleziona un colore</option>
                            <option value="1">Rosso</option>
                            <option value="2">Giallo</option>
                            <option value="3">Blu</option>
                            <option value="4">Verde</option>
                        </select>
                    </td>
                <?php endfor; ?>
                <td>
                    <button type="submit">
                        <img src="./Images/spunta.gif" alt="Invia">
                    </button>
                </td>
            </tr>
        </table>
    </form>
    
    <div><?= $feedback ?></div>

    <!-- Tabella dei tentativi -->
    <?php if (!empty($_SESSION['history'])): ?>
        <h2>Cronologia Tentativi</h2>
        <table>
            <tr>
                <th>Tentativo</th>
                <th>Colori Inseriti</th>
                <th>Neri</th>
                <th>Bianchi</th>
            </tr>
            <?php for ($i = 0; $i < count($_SESSION['history']); $i++): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <?php for ($j = 0; $j < 4; $j++): ?>
                            <img src="<?= $colori[$_SESSION['history'][$i]['colors'][$j]] ?>" alt="Colore">
                        <?php endfor; ?>
                    </td>
                    <td>
                        <?php
                        // Mostra le immagini per i "neri"
                        for ($k = 0; $k < $_SESSION['history'][$i]['black']; $k++) {
                            echo "<img src='$img_nero' alt='Nero'>";
                        }
                    echo"</td>";
                    echo"<td>";
                        // Mostra le immagini per i "bianchi"
                        for ($k = 0; $k < $_SESSION['history'][$i]['white']; $k++) {
                            echo "<img src='$img_bianco' alt='Bianco'>";
                        }
                        ?>
                    </td>
                </tr>
            <?php endfor; ?>
        </table>
    <?php endif; ?>

    
    <form method="post">
        <button type="submit" name="reset">Nuova partita</button>
    </form>
</body>
</html>
