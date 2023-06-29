<?php

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'usuarios');

// Conexión a la base de datos
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Rutas de la API
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));

// Obtener el recurso solicitado y el ID (si corresponde)
$resource = array_shift($request);
$id = array_shift($request);

// Procesar la solicitud
switch ($method) {
    case 'GET':
        if ($resource === 'users') {
            if ($id) {
                // Obtener un usuario por ID
                getUser($id);
            } else {
                // Obtener todos los usuarios
                getUsers();
            }
        } else {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        break;
    case 'POST':
        if ($resource === 'users') {
            // Crear un nuevo usuario
            createUser();
        } else {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        break;
    case 'PUT':
        if ($resource === 'users' && $id) {
            // Actualizar un usuario por ID
            updateUser($id);
        } else {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        break;
    case 'DELETE':
        if ($resource === 'users' && $id) {
            // Eliminar un usuario por ID
            deleteUser($id);
        } else {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        break;
    default:
        header("HTTP/1.1 400 Bad Request");
        exit();
}

// Obtener todos los usuarios
function getUsers()
{
    global $conn;
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $users = array();
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        header('Content-Type: application/json');
        echo json_encode($users);
    } else {
        header("HTTP/1.1 404 Not Found");
        exit();
    }
}

// Obtener un usuario por ID
function getUser($id)
{
    global $conn;
    $sql = "SELECT * FROM users WHERE id = '$id'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($user);
    } else {
        header("HTTP/1.1 404 Not Found");
        exit();
    }
}

// Crear un nuevo usuario
function createUser()
{
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['name']) && isset($data['email'])) {
        $name = $conn->real_escape_string($data['name']);
        $email = $conn->real_escape_string($data['email']);

        $sql = "INSERT INTO users (name, email) VALUES ('$name', '$email')";
        if ($conn->query($sql) === TRUE) {
            header("HTTP/1.1 201 Created");
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            exit();
        }
    } else {
        header("HTTP/1.1 400 Bad Request");
        exit();
    }
}

// Actualizar un usuario por ID
function updateUser($id)
{
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['name']) && isset($data['email'])) {
        $name = $conn->real_escape_string($data['name']);
        $email = $conn->real_escape_string($data['email']);

        $sql = "UPDATE users SET name = '$name', email = '$email' WHERE id = '$id'";
        if ($conn->query($sql) === TRUE) {
            header("HTTP/1.1 200 OK");
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            exit();
        }
    } else {
        header("HTTP/1.1 400 Bad Request");
        exit();
    }
}

// Eliminar un usuario por ID
function deleteUser($id)
{
    global $conn;
    $sql = "DELETE FROM users WHERE id = '$id'";
    if ($conn->query($sql) === TRUE) {
        header("HTTP/1.1 204 No Content");
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        exit();
    }
}

// Cerrar la conexión a la base de datos
$conn->close();

?>
