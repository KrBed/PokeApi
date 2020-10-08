<?php

const PATH_TO_SQLITE_DB = 'pokemon_db.db';
const BASE = "pokemon_api/system.php";
const ROUTE_PATHS = [
    'register' => BASE . '/register',
    'login' => BASE . '/login',
    'me' => BASE . '/me',
    'search' => BASE . "/search",
    'all_actions' => BASE . "/actions",
    'logout' => BASE . "/logout",
    'delete' => BASE. "/delete"
];

/**
 * @param string $login
 * @param PDO $db
 * @return mixed
 */
function getUserByLogin(string $login, PDO $db)
{
    $stmt = $db->prepare("SELECT * FROM user WHERE login = :login");
    $stmt->bindParam(':login', $login, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * @param PDO $db
 * @param string $sql
 * @param array $action
 * @return bool|PDOStatement
 */
function prepareActionQuery(PDO $db, string $sql, array $action)
{
    $query = $db->prepare($sql);
    $query->bindValue(':date_time', $action['date_time']);
    $query->bindValue(':searched', $action['searched']);
    $query->bindValue(':search_result', $action['search_result']);
    $query->bindValue(':user_id', $action['user_id']);
    return $query;
}

/**
 * @param string $status
 * @param string $message
 */
function sendResponseMessage(string $status, string $message): void
{
    $data = ['status' => $status, 'message' => $message];
    echo json_encode($data, JSON_THROW_ON_ERROR);
}

session_start();

$db = new PDO('sqlite:' . PATH_TO_SQLITE_DB);

$request = trim($_SERVER['REQUEST_URI'], '/');
if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
    $input = json_decode(file_get_contents('php://input'), True, 4, JSON_THROW_ON_ERROR);
}
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    return; // hendled by APACHE;
}

/**
 * @param $input
 * @param PDO $db
 */
function loginUser($input, PDO $db): void
{
    $login = $input["login"];
    $user = getUserByLogin($login, $db);
    if ($user === false) {
        unset($_SESSION['user_id']);
        sendResponseMessage("ERROR", "Failed to login user.");
    } else {
        $password_verify_result = password_verify($input["password"], $user["hashed_password"]);
        if ($password_verify_result) {
            $_SESSION['user_id'] = $user["id"];
            sendResponseMessage("OK", "Login successful.");
        } else {
            sendResponseMessage("ERROR", "Failed to login user.");
        }
    }
}

/**
 * @param $input
 * @param PDO $db
 */
function registerUser($input, PDO $db): void
{
    $password = $input["password"];
    $login = $input["login"];

    $user = getUserByLogin($login, $db);
    if ($user !== false) {
        sendResponseMessage("ERROR", "User with that login exists.");
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO user (login,hashed_password) VALUES ('$login','$hash')";

        if ($db->exec($query)) {
            $userId = $db->lastInsertId();
            $_SESSION['user_id'] = $userId;
            sendResponseMessage("OK", "Data inserted successfully.");
        } else {
            sendResponseMessage("ERROR", "Failed to create user.");
        }
    }
}

function getPokemonFromApi($search)
{
    $url = 'https://pokeapi.co/api/v2/pokemon/' . $search . '/';
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $curl_response = curl_exec($curl);
    curl_close($curl);
    if ($curl_response === "Not Found") {
        return null;
    }
    return json_decode($curl_response, true, 512, JSON_THROW_ON_ERROR);
}

function getAllActions(PDO $db)
{
    $sql = "SELECT action.*, user.id AS user_id, user.login FROM action LEFT JOIN user ON action.user_id = user.id ORDER BY action.date_time DESC LIMIT -1";
    $query = $db->query($sql);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @param PDO $db
 * @param string $action
 * @param $action_id
 * @return mixed
 */
function prepareSingleActionQuery(PDO $db, string $action, $action_id)
{
    $query = $db->prepare($action);
    $query->bindValue(':action_id', $action_id);
    return  $query;
}

switch ($request) {
    case ROUTE_PATHS['me']:
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            sendResponseMessage("ERROR", "Sesion not exists");
        } else {
            $data = ['status' => "OK", 'user_id' => $_SESSION['user_id']];
            echo json_encode($data, JSON_THROW_ON_ERROR);
        }
        break;
    case ROUTE_PATHS['login']:
        loginUser($input, $db);
        break;
    case ROUTE_PATHS['register']:
        registerUser($input, $db);
        break;
    case ROUTE_PATHS['search']:
        $search = $input["search"];

        $action = [];
        $action['user_id'] = $_SESSION['user_id'];
        $action['searched'] = $search;
        $now = new DateTime('now');
        $action['date_time'] = $now->format('Y.m.d H:i:s');
        $response = getPokemonFromApi($search);

        if ($response === null) {
            $action['search_result'] = "Not Found";
        } else {
            $action['search_result'] = "Success";
        }
        $sql = "INSERT INTO 'action'('date_time','searched','search_result','user_id') VALUES (:date_time,:searched,:search_result,:user_id)";

        $query = prepareActionQuery($db, $sql, $action);
        $actions = $query->execute();

        // prepare creature action
        if ($response !== null) {
            $moves = count($response["moves"]);
            $picture = $response["sprites"]["front_default"];
            if ($picture === null) {
                $picture = "https://placekitten.com/200/300";
            }
            $creature = ['name' => $response["name"], 'picture' => $picture, 'moves' => $moves];
            echo json_encode($creature, JSON_THROW_ON_ERROR);
        }
// getting all actions function
        break;
    case ROUTE_PATHS['all_actions']:
        $actions = getAllActions($db);
        echo json_encode($actions, JSON_THROW_ON_ERROR);

        break;
    case ROUTE_PATHS["logout"]:
        session_destroy();
        echo json_encode(["status" => "OK"], JSON_THROW_ON_ERROR);
    case ROUTE_PATHS["delete"] :
        $action_id = $input;
        $action = "SELECT * FROM action WHERE id == :action_id";
        $query = prepareSingleActionQuery($db, $action, $action_id);
        $query->execute();
        $result = $query->fetch();

        if($result["user_id"] === $_SESSION["user_id"]){
            $sql = "DELETE FROM action WHERE id = :action_id";
            $query = prepareSingleActionQuery($db, $sql, $action_id);
            $query->execute();


        }
        break;



}

