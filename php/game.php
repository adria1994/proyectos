<?php
include_once('connect.php');
include_once('generateRandom.php');

switch (@$_POST['funcion']){
    case 'create':
        $token = $_POST['token'];
        $username = $_POST['username'];
        createGame($mysqli, $username, $token);
        break;
    case 'update';
        updateGame($mysqli, $username, $token, $idPregunta);
        break;
    case 'finish';
        finish($mysqli, $username, $token);
        break;
}

function createGame($mysqli, $username, $token) {
    $array = [];
    $array['error'] = 1;
    $array['errorMessage'] = '';

    $idUser = getUserId($mysqli, $username, $token);
    if ($idUser > 0) {
        $select = "INSERT INTO `game` (`Id`, `Fecha`, `Edad`, `Id_user`) VALUES (DEFAULT, :date, NULL, :idUser)";
        $row = $mysqli->prepare($select);
        $row->execute(array(':date' => date('Y-m-d'), ':idUser' => $idUser));

        if ($row->rowCount() == 1) {
            $idGame = $mysqli->lastInsertId();
            $select = "UPDATE `user` SET `CurrentGame` = :id WHERE Username = :name AND Token = :token";
            $row = $mysqli->prepare($select);
            $row->execute(array(':id' => $idGame, ':name' => $username, ':token' => $token));
            if ($row->rowCount() == 1) {
                $array['error'] = 0;

                $array = array_merge($array, getRandomQuestion($mysqli, $idGame)[0]);
            }
        }
    } else $array['errorMessage'] = 'Sesion invalida';

    echo json_encode($array);
}

function updateGame($mysqli, $username, $token, $idPregunta, $response) {
    $array = [];
    $array['error'] = 1;
    $array['funish'] = 0;
    $array['errorMessage'] = '';

    $idUser = getUserId($mysqli, $username, $token);
    $currentGame = getCurrentGame($mysqli, $username, $token);
    if ($idUser > 0) {
        $select = "INSERT INTO `question_game` (`IdQuestion`, `IdGame`, `Response`) VALUES (:question, :game, :response)";
        $row = $mysqli->prepare($select);
        $row->execute(array(':question' => $currentGame, ':game' => $idPregunta, ':response' => $response));

        if ($row->rowCount() == 1) {
            $array['error'] = 0;
            $array = array_merge($array, getRandomQuestion($mysqli, $currentGame)[0]);
        }

    } else $array['errorMessage'] = 'Sesion invalida';

    echo json_encode($array);
}

function getRandomQuestion($mysqli, $idGame) {
    $array = [];

    $num = getRandoms(countQuestion($mysqli), getQuestionGame($mysqli, $idGame));
    $select = "SELECT * FROM question WHERE Id = :id";
    $row = $mysqli->prepare($select);
    $row->execute(array(':id' => $num));

    while ($row2 = $row->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
        $array[] = array(
            'Statement' => $row2['Statement'],
            'Answer1' => $row2['Answer1'],
            'Answer2' => $row2['Answer2'],
            'Answer3' => $row2['Answer3'],
            'Answer4' => $row2['Answer4'],
        );
    }
    return $array;
}

function countQuestion($mysqli) {
    $select = "SELECT COUNT(*) as cantidad FROM question";
    $row = $mysqli->prepare($select);
    $row->execute();
    return $row->fetch()['cantidad'];
}

function getQuestionGame($mysqli, $id) {
    $array = [];
    $select = "SELECT IdQuestion FROM question_game WHERE IdGame = :id";
    $row = $mysqli->prepare($select);
    $row->execute(array(':id' => $id));
    if($row->rowCount() > 0) {
        while ($row2 = $row->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            $array[] = $row2['IdQuestion'];
        }
    }
    return $array;
}

function getUserId($mysqli, $name, $token) {
    $select = "SELECT * FROM user WHERE Username = :name AND Token = :token";
    $row = $mysqli->prepare($select);
    $row->execute(array(':name' => $name, ':token' => $token));
    return $row->rowCount() == 1 ? $row->fetch()['Id'] : -1;
}

function getCurrentGame($mysqli, $name, $token) {
    $select = "SELECT * FROM user WHERE Username = :name AND Token = :token";
    $row = $mysqli->prepare($select);
    $row->execute(array(':name' => $name, ':token' => $token));
    return $row->rowCount() == 1 ? $row->fetch()['CurrentGame'] : -1;
}

$mysqli = null;