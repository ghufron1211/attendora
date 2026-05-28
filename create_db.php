<?php
$pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
$pdo->exec('DROP DATABASE IF EXISTS absensi');
$pdo->exec('CREATE DATABASE absensi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
echo "Database absensi created!\n";
