<?php

$inData = getRequestInfo();

$login = $inData["login"];
$password = $inData["password"];
$firstName = $inData["firstName"];
$lastName = $inData["lastName"];
$email = $inData["email"]; // Added email field

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "DB01");
if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    // Check if the login or email already exists
    $stmt = $conn->prepare("SELECT * FROM Users WHERE Login = ? OR Email = ?");
    $stmt->bind_param("ss", $login, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        returnWithError("This login or email is already taken");
        $stmt->close();
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO Users (Login, Password, firstName, lastName, Email) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $login, $password, $firstName, $lastName, $email);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $insertedUserID = $stmt->insert_id;
            returnWithInfo($firstName, $lastName, $email, $insertedUserID);
        } else {
            returnWithError("Error in registering user");
        }

        $stmt->close();
    }

    $conn->close();
}

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function sendResultInfoAsJson($obj)
{
    header('Content-type: application/json');
    echo $obj;
}

function returnWithError($err)
{
    $retValue = '{"UserID":0, "firstName":"", "lastName":"", "email":"", "error":"' . $err . '"}';
    sendResultInfoAsJson($retValue);
}

function returnWithInfo($firstName, $lastName, $email, $UserID)
{
    $retValue = '{"UserID":' . $UserID . ', "firstName":"' . $firstName . '", "lastName":"' . $lastName . '", "email":"' . $email . '", "error":""}';
    sendResultInfoAsJson($retValue);
}
?>
