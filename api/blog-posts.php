<?php
session_start();
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

mysqli_report((MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX) | MYSQLI_REPORT_STRICT);
try {
    $mysql = new mysqli("studentdb-maria.gl.umbc.edu", "j247", "j247", "j247");
} catch (Exception $e) {
    error_log($e);
}

$query = "
    SELECT 
        id,
        title,
        content,
        tags
    FROM
        blog_posts
    ORDER BY
        id DESC
";

$statement = $mysql->prepare($query);

if (!$statement->execute()) {
    http_response_code(500);
    echo json_encode(
        ["message" => "Something went wrong. Please try again."]
    );
    exit();
}

$posts = [];

$statement->bind_result($id, $title, $content, $tags);

while ($statement->fetch()) {
    array_push($posts, [
        "id" => $id,
        "title" => $title,
        "content" => $content,
        "tags" => $tags,
    ]);
}

http_response_code(200);
echo json_encode(
    [
        "posts" => $posts
    ]
);
exit();
