<?php
function respond ($status_code, $body) {
    http_response_code($status_code);
    echo json_encode($body);
    die();
}
function error ($status_code, $message) {
    respond($status_code, [ "error" => $message ]);
}
?>