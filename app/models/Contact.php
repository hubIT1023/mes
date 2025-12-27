<?php

class Contact {
	
    public $name;
    public $email;
    public $message;

    public function __construct($name, $email, $message) {
        $this->name = $name;
        $this->email = $email;
        $this->message = $message;
    }

    public function save() {
        // For now, just log it — you can later connect to a DB
        $file = __DIR__ . '/../../storage/contacts.txt';
        file_put_contents($file, "{$this->name} | {$this->email} | {$this->message}\n", FILE_APPEND);
    }
}