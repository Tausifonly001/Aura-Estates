<?php
require_once __DIR__ . '/../../../src/config/auth.php';
Auth::logout(Auth::getBasePrefix() . '/');
