<?php
// config.php sekarang hanya "alias" ke koneksi.php
// supaya HANYA ADA SATU sumber koneksi database di seluruh project (tidak duplikat).
require_once __DIR__ . '/koneksi.php';
