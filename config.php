<?php

// Menyertakan file versi dan konfigurasi Neo Feeder
include_once 'component/version.php';

// Mendefinisikan path dan URL root
$rootPath = $_SERVER['DOCUMENT_ROOT'];
$thisPath = dirname($_SERVER['PHP_SELF']);
$weburl = str_replace($rootPath, '', $thisPath);

// Konfigurasi Database Neo Integrator
$db_server   = "localhost";
$db_username = "root";
$db_password = "";
$db_database = "feeder_api";

// Membuat koneksi ke database
$db = mysqli_connect($db_server, $db_username, $db_password, $db_database);

// Memeriksa koneksi
if (!$db) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Konfigurasi API NEO-FEEDER / PDDIKTI
$urlfeeder  = "https://soap-feeder.telkomuniversity.ac.id";   // contoh http://192.168.1.1:8100
$userfeeder = "pahalawanti@telu.ac.id";   // User PDDIKTI
$passfeeder = "K1t3r3t500!";   // Pass PDDIKTI

// Menginisialisasi kelas Ws_pddikti untuk berkomunikasi dengan API Feeder
include_once 'component/ws_pddikti.php';
$ws = new Ws_pddikti("$urlfeeder/ws/live2.php", $userfeeder, $passfeeder);

// Pengaturan Zona Waktu dan ID Waktu
date_default_timezone_set("Asia/Bangkok");
$date = date('Y-m-d h:i:s', time());
$id_insert = time();

// Menyertakan file fungsi tambahan jika diperlukan
include_once 'component/function.php';

// Mengatur batas waktu eksekusi skrip
ini_set('max_execution_time', '100000');

?>
