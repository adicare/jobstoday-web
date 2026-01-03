<?php
require_once "../config/config.php";

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

function out($arr){
  echo json_encode($arr);
  exit;
}

switch($type){

  /* ================= STATES ================= */
  case 'states':
    $res = $conn->query("
      SELECT DISTINCT state 
      FROM india_location 
      ORDER BY state ASC
    ");
    $data=[];
    while($r=$res->fetch_assoc()){
      $data[]=$r['state'];
    }
    out($data);
  break;

  /* ================= DISTRICTS ================= */
  case 'districts':
    $state = $_GET['state'] ?? '';
    $stmt = $conn->prepare("
      SELECT DISTINCT district 
      FROM india_location 
      WHERE state=? 
      ORDER BY district ASC
    ");
    $stmt->bind_param("s",$state);
    $stmt->execute();
    $res=$stmt->get_result();
    $data=[];
    while($r=$res->fetch_assoc()){
      $data[]=$r['district'];
    }
    out($data);
  break;

  /* ================= TEHSILS ================= */
  case 'tehsils':
    $state=$_GET['state']??'';
    $district=$_GET['district']??'';
    $stmt=$conn->prepare("
      SELECT DISTINCT tehsil 
      FROM india_location 
      WHERE state=? AND district=? 
      ORDER BY tehsil ASC
    ");
    $stmt->bind_param("ss",$state,$district);
    $stmt->execute();
    $res=$stmt->get_result();
    $data=[];
    while($r=$res->fetch_assoc()){
      $data[]=$r['tehsil'];
    }
    out($data);
  break;

  /* ================= VILLAGES + LAT/LNG ================= */
  case 'villages':
    $state=$_GET['state']??'';
    $district=$_GET['district']??'';
    $tehsil=$_GET['tehsil']??'';
    $stmt=$conn->prepare("
      SELECT village, latitude, longitude
      FROM india_location
      WHERE state=? AND district=? AND tehsil=?
      ORDER BY village ASC
    ");
    $stmt->bind_param("sss",$state,$district,$tehsil);
    $stmt->execute();
    $res=$stmt->get_result();
    $data=[];
    while($r=$res->fetch_assoc()){
      $data[]=$r;
    }
    out($data);
  break;

  default:
    out([]);
}
