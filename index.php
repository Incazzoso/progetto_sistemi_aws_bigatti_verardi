<?php
session_start();

// Configurazione Database (i valori corrisponderanno al futuro container Docker)
$host = 'db'; 
$dbname = 'progetto_sistemi';
$user = 'root';
$pass = 'rootpassword'; 

$error_msg = '';

// Gestione del Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Connessione sicura al DB con PDO (previene SQL Injection)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Errore di connessione al database: " . $e->getMessage());
}

// Logica di Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica sicura della password con l'hash salvato
    if ($user_data && password_verify($password, $user_data['password_hash'])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user_data['username'];
        header("Location: index.php"); // Evita il reinvio del form
        exit();
    } else {
        $error_msg = "Credenziali non valide. Riprova.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progetto Sistemi - Relazione</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow p-4 rounded-4" style="width: 100%; max-width: 400px;">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary">Accesso Riservato</h2>
                <p class="text-muted">Inserisci le credenziali fornite per visualizzare la relazione.</p>
            </div>
            
            <?php if ($error_msg): ?>
                <div class="alert alert-danger" role="alert"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary w-100 fw-bold">Accedi</button>
            </form>
        </div>
    </div>

<?php else: ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Progetto Sistemi & Info</a>
            <div class="d-flex align-items-center text-white">
                <span class="me-3">Benvenuto, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
                <a href="index.php?logout=true" class="btn btn-sm btn-danger">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <div class="row mt-4">
            <div class="col-12">
                <h1 class="display-5 fw-bold border-bottom pb-2">Relazione di Progetto</h1>
                <p class="lead">Progetto trasversale per la creazione di un'infrastruttura Cloud sicura su AWS.</p>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card shadow-sm mb-4 h-100">
                    <div class="card-body">
                        <h4 class="card-title text-primary">Tecnologie Utilizzate</h4>
                        <ul class="list-group list-group-flush mt-3">
                            <li class="list-group-item"><strong>AWS EC2:</strong> Hosting del server virtuale.</li>
                            <li class="list-group-item"><strong>Docker:</strong> Containerizzazione di Nginx, PHP e MySQL per un ambiente isolato e scalabile.</li>
                            <li class="list-group-item"><strong>PHP 8 & PDO:</strong> Logica di backend sicura e prevenzione delle SQL injection.</li>
                            <li class="list-group-item"><strong>Bootstrap 5:</strong> Framework CSS per rendere la Single Page Application responsiva.</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="card-title text-primary">Scelte Progettuali e Sicurezza</h4>
                        <p class="mt-3">
                            Per garantire la sicurezza richiesta dalla consegna, il sito è stato sviluppato adottando diverse precauzioni:
                        </p>
                        <ul>
                            <li><strong>Crittografia delle Password:</strong> Le password nel database non sono salvate in chiaro, ma viene generato un hash crittografico univoco utilizzando l'algoritmo <code>bcrypt</code> (standard di sicurezza).</li>
                            <li><strong>Connessione Sicura:</strong> L'intero traffico web viene forzato su protocollo <strong>HTTPS</strong>. È stato configurato un Reverse Proxy tramite Nginx che redireziona automaticamente le richieste HTTP verso HTTPS (Bonus).</li>
                            <li><strong>Prevenzione SQL Injection:</strong> Tutte le interrogazioni al database utilizzano i <em>Prepared Statements</em> (PDO), che separano i dati dalla struttura della query.</li>
                            <li><strong>Certificato Self-Signed:</strong> È stato generato un certificato SSL locale montato direttamente nel container Nginx per criptare il canale di comunicazione.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>