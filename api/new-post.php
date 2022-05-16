<?php
session_start();
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

mysqli_report((MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX) | MYSQLI_REPORT_STRICT);
try {
    $mysql = new mysqli("studentdb-maria.gl.umbc.edu", "j247", "j247", "j247");
} catch (Exception $e) {
    error_log($e);
}

$body = json_decode(file_get_contents("php://input"));

// Is the title length out of range?
if (strlen($body->title) < 1 || strlen($body->title) > 256) {
    http_response_code(400);
    echo json_encode(
        ["message" => "Title must be 1-256 characters."]
    );
    exit();
}

// Is the content too long?
if (strlen($body->content) > 65535) {
    http_response_code(400);
    echo json_encode(
        ["message" => "Post content must be less than 65535 characters."]
    );
    exit();
}

// Is the tags list length out of range?
if (strlen($body->tags) < 1 || strlen($body->tags) > 256) {
    http_response_code(400);
    echo json_encode(
        ["message" => "Tag list must be 1-256 characters."]
    );
    exit();
}

// Insert the post...
$query = "
    INSERT INTO
        blog_posts( title, content, tags )
    VALUES( ?, ?, ? )
";
$statement = $mysql->prepare($query);
$statement->bind_param(
    "sss",
    $body->title,
    $body->content,
    $body->tags
);

if (!$statement->execute()) {
    http_response_code(500);
    echo json_encode(
        ["message" => "Something went wrong. Please try again."]
    );
    exit();
} else {
    http_response_code(200);
    echo json_encode(
        ["message" => "Post created successfully."]
    );
    exit();
}
