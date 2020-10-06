<?php
session_start();
const PATH_TO_SQLITE_DB = 'pokemon_db.db';
const BASE = "pokemon_api/system.php";
const ROUTE_PATHS = ['register' => BASE.'/register', 'login' => BASE.'/login'];

$db = new PDO('sqlite:'.PATH_TO_SQLITE_DB);

$request = trim($_SERVER['REQUEST_URI'], '/');
$input = json_decode(file_get_contents('php://input'), True);

if($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    return;
}

switch ($request) {
    case ROUTE_PATHS['login']:

        $email = $input["email"];
        $result = $db->query('SELECT * FROM user')->fetchAll();
        $stmt = $db->prepare("SELECT * FROM user WHERE email = :email");
        $stmt->bindParam(':email',$email,PDO::PARAM_STR);

        $stmt -> execute();
        /* fetch all results */
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        $password_verify_result = password_verify($input["password"],$res["hashed_password"]);
        if($password_verify_result){
            $_SESSION['user_id'] = $res["id"];
            $data = ['status' => "OK", 'message'=>"Login succesful"];
            echo json_encode($data);
        }else{
            $data = ['status' => "ERROR", 'message'=>"Failed to login user."];
            echo json_encode($data);
            unset( $_SESSION['user_id'] );
        }
        break;

    case ROUTE_PATHS['register']:
        $password = $input["password"];
        $email = $input["email"];
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO user (email,hashed_password) VALUES ('$email','$hash')";
        if ($db->exec($query)) {
            $data = ['status' => "OK", 'message'=>"Data inserted successfully."];
            echo json_encode($data);
        } else {
            $data = ['status' => "ERROR", 'message'=>"Failed to create user."];
            echo json_encode($data);
        }
        break;
}
// run SQL statement
//$result = $pdo->query($sql);

// die if SQL statement failed
//if (!$result) {
//    http_response_code(404);
////    die(mysqli_error($con));
//}

//if ($method == 'GET') {
//    if (!$id) echo '[';
//    for ($i = 0; $i < mysqli_num_rows($result); $i++) {
//        echo ($i > 0 ? ',' : '') . json_encode(mysqli_fetch_object($result));
//    }
//    if (!$id) echo ']';
//} elseif ($method == 'POST') {
//    echo json_encode($result);
//} else {
//    echo mysqli_affected_rows($con);
//}

//$con->close();
