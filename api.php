<?php
if (!isset($_SERVER['CONTENT_TYPE']) || stripos($_SERVER['CONTENT_TYPE'], 'application/json') === FALSE) {
  http_response_code(500);
  die('Content-Type must be application/json.');
}

$ini_config = parse_ini_file('config.ini', FALSE);
if ($ini_config === FALSE) {
  http_response_code(500);
  die('Config file not found.');
}

if (empty($_GET['api_token'])) {
  http_response_code(403);
  die('GET parameter "api_token" missing.');
}

if ($_GET['api_token'] !== $ini_config['api_token']) {
  http_response_code(403);
  die('Wrong API token.');
}

$body = json_decode(file_get_contents("php://input"), true);
if (!is_array($body)) {
  http_response_code(500);
  die('Failed to decode JSON object.');
}

if (!isset($_GET['id'])) {
  http_response_code(404);
  die('GET parameter "id" missing.');
}
$id = $_GET['id'];

$id_configs = json_decode(@file_get_contents('ids.json'), true);
$config = in_array($id, array_keys($id_configs)) ? $id_configs[$id] : ['separator' => ',', 'filepath' => "data/{$id}.csv"];

switch(strtoupper($_SERVER['REQUEST_METHOD'])) {
case 'POST':
  $text = implode($config['separator'], $body['fields']);
  file_put_contents($config['filepath'], "$text\n", FILE_APPEND | LOCK_EX);

  http_response_code(200);
  exit;
case 'DELETE':
  if (!isset($body['id']) || !isset($body['columnIdx']) || !is_int($body['columnIdx'])) {
    http_response_code(404);
    die('Body parameter(s) missing.');
  }

  $lines = explode("\n", @file_get_contents($config['filepath']));
  $new_lines = [];
  foreach ($lines as $line) {
    $fields = explode($config['separator'], $line);
    if ($body['columnIdx'] < count($fields) && $fields[$body['columnIdx']] === $body['id']) {
      continue;
    }
    $new_lines[] = $line;
  }

  $text = implode("\n", $new_lines);
  file_put_contents($config['filepath'], $text, LOCK_EX);

  http_response_code(200);
  exit;
default:
  http_response_code(400);
  die('Invalid request method ' . $_SERVER['REQUEST_METHOD']);
}
