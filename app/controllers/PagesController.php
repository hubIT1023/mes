<?php
// PagesController.php

// Require the BASE Controller class (not this file!)
require_once __DIR__ . '/Controller.php';

class PagesController extends Controller {
    public function welcome() {
        $this->view('welcome_page', [
            'title' => 'Welcome to hubIT.online',
            'tagline' => 'Your reliable IT partner for digital solutions.'
        ]);
    }

    public function about() {
        $this->view('about', [
            'title' => 'About Us',
            'content' => 'We build modern web applications using PHP MVC architecture.'
        ]);
    }

    public function signin() {
        $this->view('signin', ['title' => 'Sign In']);
    }

    public function register() {
        $this->view('register', ['title' => 'Register']);
    }

    public function canvas() {
        $this->view('canvas', ['title' => 'Canvas']);
    }

    public function mms_admin() {
        $this->view('mms_admin', ['title' => 'mms_Admin']);
    }

    public function dashboard_admin() {
        $this->view('dashboard_admin', ['title' => 'Dashboard_Admin']);
    }

    public function demo_dashboard() {
        $this->view('demo/demo_dashboard', ['title' => 'Demo_Dashboard']);
    }

    public function demo_mes() {
        $this->view('demo/demo_mes', ['title' => 'Demo_MES']);
    }
}