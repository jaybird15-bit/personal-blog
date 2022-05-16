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

// TODO: Sign up the user
// 1. Input validation
//    a. Is the email a valid email address?
//    b. Is the password secure (8+ characters)
// 2. Check if the email is already in use
// 3. Create a user in the database
//      NOTE: Must hash password
// 4. Login

// Is the email invalid? (i.e. does it have no '@' character, etc...)
if (filter_var($body->email, FILTER_VALIDATE_EMAIL) == false) {
    http_response_code(400);
    echo json_encode(
        ["message" => "Email is invalid."]
    );
    exit();
}

// Is the password too weak?
if (strlen($body->password) < 8) {
    http_response_code(400);
    echo json_encode(
        ["message" => "Password must be 8+ characters."]
    );
    exit();
}

$query = "
    SELECT *
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

$count = $statement->num_rows();
$statement->fetch();

// Is the email already in use?
if ($count > 0) {
    http_response_code(400);
    echo json_encode(
        ["message" => "Email already in use."]
    );
    exit();
}

// Insert the user...
$query = "
    INSERT INTO
        blog_users( email, password )
    VALUES( ?, ? )
";
$statement = $mysql->prepare($query);
$statement->bind_param("ss", $body->email, $body->password);
if (!$statement->execute()) {
    http_response_code(500);
    echo json_encode(
        ["message" => "Something went wrong. Please try again."]
    );
    exit();
}

// Log the user in (check for this later)...
$_SESSION["email"] = $body->email;

http_response_code(200);
echo json_encode(
    ["message" => "Signup successful."]
);
exit();
