<?php
session_start();
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

mysqli_report((MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX) | MYSQLI_REPORT_STRICT);
try {
    $mysql = new mysqli("studentdb-maria.gl.umbc.edu", "j247", "j247", "j247");
} catch (Exception $e) {
    error_log($e);
}

if (!isset($_SESSION["email"])) {
    http_response_code(200);
    echo json_encode(
        null
    );
    exit();
}

$query = "
    SELECT 
        email
    FROM
        blog_users
    WHERE
        email = ?
";

$statement = $mysql->prepare($query);
$statement->bind_param(
    "s",
    $_SESSION["email"]
);

if (!$statement->execute()) {
    http_response_code(500);
    echo json_encode(
        ["message" => "Something went wrong. Please try again."]
    );
    exit();
}

$statement->bind_result($email);
$statement->fetch();

// Is the user is logged in?
if ($email) {
    http_response_code(200);
    echo json_encode(
        ["email" => $email]
    );
    exit();
} else {
    http_response_code(200);
    echo json_encode(
        null
    );
    exit();
}
