<?php

const PATH_TO_SQLITE_DB = 'pokemon_db.db';
const BASE = "pokemon_api/system.php";
const ROUTE_PATHS = ['register' => BASE . '/register', 'login' => BASE . '/login', 'me' => BASE . '/me', 'action' => BASE . "/action",'logout'=>BASE . "/logout"];

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
function prepare_action_query(PDO $db, string $sql, array $action)
{
    $query = $db->prepare($sql);
    $query->bindValue(':date_time', $action['date_time']);
    $query->bindValue(':searched', $action['searched']);
    $query->bindValue(':search_result', $action['search_result']);
    $query->bindValue(':user_id', $action['user_id']);
    return $query;
}

session_start();

$db = new PDO('sqlite:' . PATH_TO_SQLITE_DB);

$request = trim($_SERVER['REQUEST_URI'], '/');
if(strtoupper ($_SERVER["REQUEST_METHOD"]) === "POST") {
    $input = json_decode(file_get_contents('php://input'), True, 4, JSON_THROW_ON_ERROR);
}
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    return; // hendled by APACHE;
}

switch ($request) {

    case ROUTE_PATHS['me']:
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            $data = ['status' => "ERROR", 'message' => "Session not exists"];
            echo json_encode($data, JSON_THROW_ON_ERROR);
        }else {
            $data = ['status' => "OK", 'user_id' => $_SESSION['user_id']];
            echo json_encode($data, JSON_THROW_ON_ERROR);
        }
        break;
    case ROUTE_PATHS['login']:
        $login = $input["login"];
        $user = getUserByLogin($login, $db);
        if ($user === false) {
            unset($_SESSION['user_id']);
            $data = ['status' => "ERROR", 'message' => "Failed to login user."];
            echo json_encode($data, JSON_THROW_ON_ERROR);
        } else {
            $password_verify_result = password_verify($input["password"], $user["hashed_password"]);
            if ($password_verify_result) {
                $_SESSION['user_id'] = $user["id"];
                $data = ['status' => "OK", 'message' => "Login succesful"];
                echo json_encode($data, JSON_THROW_ON_ERROR);
            } else {
                $data = ['status' => "ERROR", 'message' => "Failed to login user."];
                echo json_encode($data, JSON_THROW_ON_ERROR);
            }
        }
        break;
    case ROUTE_PATHS['register']:
        $password = $input["password"];
        $login = $input["login"];

        $user = getUserByLogin($login, $db);
        if ($user !== false) {
            $data = ['status' => "ERROR", 'message' => "User with that login exists"];
            echo json_encode($data, JSON_THROW_ON_ERROR);
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO user (login,hashed_password) VALUES ('$login','$hash')";

            if ($db->exec($query)) {
                $userId = $db->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $data = ['status' => "OK", 'message' => "Data inserted successfully."];
                echo json_encode($data, JSON_THROW_ON_ERROR);
            } else {
                $data = ['status' => "ERROR", 'message' => "Failed to create user."];
                echo json_encode($data);
            }
        }
        break;
    case
    ROUTE_PATHS['action']:
        $search = $input["search"];

        $url = 'https://pokeapi.co/api/v2/pokemon/' . $search;
        $action = [];
        $action['user_id'] = 1; // do poprawy jak ogarnę sesję
        $action['searched'] = $search;
        $now = new DateTime('now');
        $action['date_time'] = $now->format('Y:M:d H:i:s');
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        $response = json_decode($curl_response, true);
        curl_close($curl);
        if ($curl_response === "Not Found") {
            $action['search_result'] = "Not Found";
        } else {
            $action['search_result'] = "Success";
        }
        $sql = "INSERT INTO 'action'('date_time','searched','search_result','user_id') VALUES (:date_time,:searched,:search_result,:user_id)";
        $query = prepare_action_query($db, $sql, $action);

        $result = $query->execute();
        if ($result) {
            echo 'inserted';
        }
        $creature = ['name'=>$response["name"],'picture'=>$response["sprites"]["front_default"],'moves'=>$response["moves"]];

        echo json_encode($creature);
        break;
    case ROUTE_PATHS["logout"]:
        session_destroy();
        echo json_encode(["status" => "OK"], JSON_THROW_ON_ERROR);
}

