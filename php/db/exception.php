<?php
class RowNotFoundException extends Exception {
    public function __construct ($message) {
        parent::__construct($message, 404, null);
    }
}

class InvalidOperationException extends Exception {
    public function __construct ($message) {
        parent::__construct($message, 400, null);
    }
}
?>