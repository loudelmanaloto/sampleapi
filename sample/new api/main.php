<?php
require_once "./config/Connection.php";
require_once "./models/Global.php";
require_once "./models/Auth.php";
require_once "./models/Get.php";
require_once "./models/Procedural.php";
require_once "./models/Post.php";

$db = new Connection();
$pdo = $db->connect();

$auth = new Auth($pdo);
$get = new Get($pdo);
$post = new Post($pdo);

if (isset($_REQUEST['request'])) {
	$req = explode('/', rtrim($_REQUEST['request'], '/'));
} else {
	$req = array("errorcatcher");
}

switch ($_SERVER['REQUEST_METHOD']){
    case 'POST':
        switch ($req[0]){
            case 'login':
                $d = json_decode(file_get_contents("php://input"));
                echo json_encode($auth->login($d));
            break;

            case 'adduser':
                $d = json_decode(file_get_contents("php://input"));
                echo json_encode($auth->adduser($d));
            break;   

            case 'getemployee':
                if($auth->authorized()){
                    if(count($req)>1){
                        echo json_encode($get->getInfo($req[1]));
                    }
                    else{
                        echo json_encode($get->getInfo(null));
                    }   
                }
                else{
                    echo errMsg(401);
                }    
            break;
            case 'addemployee':
                $d = json_decode(file_get_contents("php://input"));
                echo json_encode($post->addemployee($d));
            break;
            case 'editemployee':
                $d = json_decode(file_get_contents("php://input"));
                if(count($req)>1){
                    echo json_encode($post->editemployee($d, $req[1]));
                }
                else{
                    errMsg(400);
                }
                
            break;
            case 'deleteemployee':
                if(count($req)>1){
                    echo json_encode($post->delete($req[1]));
                }
                else{
                    echo errMsg(400);
                }
            break;
            case 'update':
                $d = json_decode(file_get_contents("php://input"));   
                echo json_encode($post->update($d, $req[1]));      
            break;
            case 'insert':
                $d = json_decode(file_get_contents("php://input"));
                echo json_encode($post->insert($d));
            default:
                echo errMsg(400);
            break;

        }
    break;
    default:
        echo errMsg(403);
    break;
}


?>