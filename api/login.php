<?php
session_start();
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

mysqli_report((MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX) | MYSQLI_REPORT_STRICT);
try {
    $mysql = new mysqli("studentdb-maria.gl.umbc.edu", "j247", "j247", "j247");
} catch (Exception $e) {
    error_log($e);
}

// email, password
$body = json_decode(file_get_contents("php://input"));

// Is the email invalid? (i.e. does it have no '@' character, etc...)
if (filter_var($body->email, FILTER_VALIDATE_EMAIL) == false) {
    http_response_code(400);
    echo json_encode(
        ["message" => "Email is invalid."]
    );
    exit();
}

$query = "
    SELECT 
        password
    FROM
        blog_users
    WHERE
        email = ?
";
$statement = $mysql->prepare($query);
$statement->bind_param(
    "s",
    $body->email
);

if (!$statement->execute()) {
    http_response_code(500);
    echo json_encode(
        ["message" => "Something went wrong. Please try again."]
    );
    exit();
}

$statement->bind_result($password);
$statement->fetch();

// Does that user exist?
if ($password) {

    // Does the password hash match the given password?
    if ($body->password == $password) {

        // Log the user in (check for this later)...
        $_SESSION["email"] = $body->email;

        http_response_code(200);
        echo json_encode(
            ["message" => "Login successful."]
        );
        exit();
    }
}

http_response_code(400);
echo json_encode(
    ["message" => "Incorrect email/password."]
);
exit();
