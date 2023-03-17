<?php
require_once '../../library/functions.php';

if (empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'), true);
  };

if (isset($_POST['sect_id'])){
  $sectID = filter_var($_POST['sect_id'], FILTER_SANITIZE_NUMBER_INT);
  $sectInfo = getSectInfo($sectID);
  echo json_encode($sectInfo, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
}