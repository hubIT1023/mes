<?php
require_once __DIR__ . '/../models/Contact.php';

class ContactController {
    public function index() {
        include __DIR__ . '/../views/contact.php';
    }

    public function submit() {
		
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$name || !$email || !$message) {
            $error = "All fields are required.";
            include __DIR__ . '/../views/contact.php';
            return;
        }

        // Normally you'd store in DB or send an email
        $contact = new Contact($name, $email, $message);
        $contact->save();

        $success = "Thank you, $name! Your message has been received.";
		
        include __DIR__ . '/../views/contact.php';
    }
}