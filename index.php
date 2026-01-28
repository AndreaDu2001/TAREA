<?php

echo "<h2>Render + Railway funcionando 🚀</h2>";

$host = getenv("MYSQLHOST");
$port = getenv("MYSQLPORT");
$user = getenv("MYSQLUSER");
$pass = getenv("MYSQLPASSWORD");
$db   = getenv("MYSQLDATABASE");

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "<p>Conexión a MySQL en Railway: ✅</p>";

    $stmt = $pdo->query("SELECT * FROM products");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Productos</h3>";

    if (!$rows) {
        echo "<p>No hay registros en la tabla</p>";
    } else {
        echo "<ul>";
        foreach ($rows as $row) {
            echo "<li>ID {$row['id']} - {$row['data']}</li>";
        }
        echo "</ul>";
    }

} catch (Exception $e) {
    echo "<p>Error de conexión:</p>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Cargar variables de entorno
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file);
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line) && strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Configuración de conexiones
$redis_host = getenv('REDIS_HOST') ?: 'localhost';
$redis_port = getenv('REDIS_PORT') ?: 6379;
$redis_password = getenv('REDIS_PASSWORD') ?: '';
$redis_channel = getenv('REDIS_CHANNEL') ?: 'channel1';

$mysql_host = getenv('MYSQL_HOST') ?: 'localhost';
$mysql_user = getenv('MYSQL_USERNAME') ?: 'root';
$mysql_password = getenv('MYSQL_PASSWORD') ?: '';
$mysql_database = getenv('MYSQL_DATABASE') ?: 'service_db';
$mysql_table = getenv('MYSQL_TABLE') ?: 'products';

// Clase para manejar Redis
class RedisConnection {
    private $connection;
    private $host;
    private $port;
    private $password;

    public function __construct($host, $port, $password) {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
    }

    public function connect() {
        try {
            $this->connection = new Redis();
            $this->connection->connect($this->host, $this->port);
            if (!empty($this->password)) {
                $this->connection->auth($this->password);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function set($key, $value) {
        if ($this->connection) {
            $this->connection->set($key, $value);
        }
    }

    public function get($key) {
        if ($this->connection) {
            return $this->connection->get($key);
        }
        return null;
    }

    public function delete($key) {
        if ($this->connection) {
            $this->connection->del($key);
        }
    }

    public function disconnect() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// Función para obtener datos de MySQL
function getData($mysql_host, $mysql_user, $mysql_password, $mysql_database, $mysql_table) {
    try {
        $conn = new mysqli($mysql_host, $mysql_user, $mysql_password, $mysql_database);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT data FROM $mysql_table";
        $result = $conn->query($sql);

        if ($result) {
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $conn->close();
            return $data;
        }
        $conn->close();
        return [];
    } catch (Exception $e) {
        return false;
    }
}

// Función para insertar datos en MySQL
function insertData($mysql_host, $mysql_user, $mysql_password, $mysql_database, $mysql_table, $data) {
    try {
        $conn = new mysqli($mysql_host, $mysql_user, $mysql_password, $mysql_database);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        $data_escaped = $conn->real_escape_string($data);
        $sql = "INSERT INTO $mysql_table (data) VALUES ('$data_escaped')";
        
        if ($conn->query($sql) === TRUE) {
            $conn->close();
            return true;
        }
        $conn->close();
        return false;
    } catch (Exception $e) {
        return false;
    }
}

// Obtener la ruta solicitada
$request_uri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$route = end($request_uri);

// Si es una solicitud POST o GET desde el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create') {
        $input_data = $_POST['data'] ?? '';

        if (empty($input_data)) {
            echo json_encode(['message' => 'failure', 'error' => 'missing data']);
            exit;
        }

        try {
            // Conectar a Redis
            $redis = new RedisConnection($redis_host, $redis_port, $redis_password);
            if ($redis->connect()) {
                // Eliminar caché anterior
                $redis->delete('key');
                $redis->disconnect();
            }

            // Insertar en MySQL
            if (insertData($mysql_host, $mysql_user, $mysql_password, $mysql_database, $mysql_table, $input_data)) {
                echo json_encode(['message' => 'success']);
            } else {
                echo json_encode(['message' => 'failure', 'error' => 'failed to insert data']);
            }
        } catch (Exception $e) {
            echo json_encode(['message' => 'failure', 'error' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'get') {
        try {
            $redis = new RedisConnection($redis_host, $redis_port, $redis_password);
            
            // Intenta obtener del caché
            if ($redis->connect()) {
                $cached_data = $redis->get('key');
                if ($cached_data) {
                    $cached = json_decode($cached_data, true);
                    echo json_encode(['message' => 'success', 'isCached' => 'yes', 'data' => $cached['data']]);
                    $redis->disconnect();
                    exit;
                }
                $redis->disconnect();
            }

            // Si no está en caché, obtener de MySQL
            $data = getData($mysql_host, $mysql_user, $mysql_password, $mysql_database, $mysql_table);

            if ($data !== false) {
                // Guardar en caché
                $redis = new RedisConnection($redis_host, $redis_port, $redis_password);
                if ($redis->connect()) {
                    $cache_value = json_encode(['isCached' => 'yes', 'data' => $data]);
                    $redis->set('key', $cache_value);
                    $redis->disconnect();
                }

                echo json_encode(['message' => 'success', 'isCached' => 'no', 'data' => $data]);
            } else {
                echo json_encode(['message' => 'failure', 'error' => 'failed to fetch data']);
            }
        } catch (Exception $e) {
            echo json_encode(['message' => 'failure', 'error' => $e->getMessage()]);
        }
        exit;
    }
}

// Si es una solicitud JSON (AJAX)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $json_data = json_decode(file_get_contents('php://input'), true);
    $action = $json_data['action'] ?? '';

    if ($action === 'create') {
        $input_data = $json_data['data'] ?? '';

        if (empty($input_data)) {
            echo json_encode(['message' => 'failure', 'error' => 'missing data']);
            exit;
        }

        try {
            $redis = new RedisConnection($redis_host, $redis_port, $redis_password);
            if ($redis->connect()) {
                $redis->delete('key');
                $redis->disconnect();
            }

            if (insertData($mysql_host, $mysql_user, $mysql_password, $mysql_database, $mysql_table, $input_data)) {
                echo json_encode(['message' => 'success']);
            } else {
                echo json_encode(['message' => 'failure', 'error' => 'failed to insert data']);
            }
        } catch (Exception $e) {
            echo json_encode(['message' => 'failure', 'error' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'get') {
        try {
            $redis = new RedisConnection($redis_host, $redis_port, $redis_password);

            if ($redis->connect()) {
                $cached_data = $redis->get('key');
                if ($cached_data) {
                    $cached = json_decode($cached_data, true);
                    echo json_encode(['message' => 'success', 'isCached' => 'yes', 'data' => $cached['data']]);
                    $redis->disconnect();
                    exit;
                }
                $redis->disconnect();
            }

            $data = getData($mysql_host, $mysql_user, $mysql_password, $mysql_database, $mysql_table);

            if ($data !== false) {
                $redis = new RedisConnection($redis_host, $redis_port, $redis_password);
                if ($redis->connect()) {
                    $cache_value = json_encode(['isCached' => 'yes', 'data' => $data]);
                    $redis->set('key', $cache_value);
                    $redis->disconnect();
                }

                echo json_encode(['message' => 'success', 'isCached' => 'no', 'data' => $data]);
            } else {
                echo json_encode(['message' => 'failure', 'error' => 'failed to fetch data']);
            }
        } catch (Exception $e) {
            echo json_encode(['message' => 'failure', 'error' => $e->getMessage()]);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Demo - PHP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        main {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 800px;
            width: 100%;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2.5rem;
        }

        section {
            margin-bottom: 40px;
        }

        form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: center;
        }

        input[type="text"] {
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            width: 300px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }

        button, input[type="submit"] {
            padding: 12px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        button:hover, input[type="submit"]:hover {
            background: #764ba2;
        }

        .button-container {
            text-align: center;
            margin: 20px 0;
        }

        .get-button {
            margin-top: 20px;
        }

        p {
            text-align: justify;
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        h3 {
            text-align: center;
            color: #333;
            margin: 40px 0 20px 0;
            font-size: 1.5rem;
        }

        pre {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            overflow-x: auto;
            border-left: 4px solid #667eea;
            color: #333;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .loading {
            text-align: center;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <main>
        <h2>Service Demo</h2>
        
        <section>
            <form id="dataForm" onsubmit="handleSubmit(event)">
                <input 
                    type="text" 
                    id="inputData" 
                    placeholder="Please write something"
                    required
                />
                <input type="submit" value="Insert" />
            </form>

            <div class="button-container">
                <button onclick="getData()" class="get-button">Get data</button>
            </div>

            <p>
                Try creating a data using input. Then click on "get data" twice to see
                the magic. Keep an eye on the isCached property when you input new data.
            </p>
        </section>

        <section>
            <h3>Output</h3>
            <pre id="output">create new data</pre>
        </section>
    </main>

    <script>
        const baseUrl = window.location.href.split('?')[0];
        let output = document.getElementById('output');

        async function handleSubmit(event) {
            event.preventDefault();
            const inputData = document.getElementById('inputData').value;

            try {
                const response = await fetch(baseUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'create',
                        data: inputData
                    })
                });

                const result = await response.json();
                console.log({ message: 'posted data', result });
                document.getElementById('inputData').value = '';
                output.textContent = JSON.stringify(result, null, 2);
            } catch (error) {
                console.error({ message: 'failed creating data', error });
                output.textContent = JSON.stringify({ message: 'failure', error: error.message }, null, 2);
            }
        }

        async function getData() {
            output.textContent = 'Loading...';
            
            try {
                const response = await fetch(baseUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'get'
                    })
                });

                const result = await response.json();
                console.log({ message: 'got data', result });
                output.textContent = JSON.stringify(result, null, 2);
            } catch (error) {
                console.error({ message: 'failed fetching data', error });
                output.textContent = JSON.stringify({ message: 'failure', error: error.message }, null, 2);
            }
        }
    </script>
</body>
</html>

