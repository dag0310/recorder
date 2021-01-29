<?php
if (! isset($_SERVER['CONTENT_TYPE']) || stripos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
  throw new Exception('Content-Type must be application/json');
}
$body = json_decode(file_get_contents("php://input"), true);
if (!is_array($body)) {
  throw new Exception('Failed to decode JSON object');
}

if (!isset($_GET['id'])) {
  throw new Exception('Not found');
}
$id = $_GET['id'];

$id_configs = json_decode(@file_get_contents('data/ids.json'), true);
$config = in_array($id, array_keys($id_configs)) ? $id_configs[$id] : ['separator' => ',', 'filepath' => "data/{$id}.csv"];

switch(strtoupper($_SERVER['REQUEST_METHOD'])) {
case 'POST':
  $text = implode($config['separator'], $body['fields']);
  file_put_contents($config['filepath'], "$text\n", FILE_APPEND | LOCK_EX);

  http_response_code(200);
  exit;
case 'DELETE':
  if (!isset($body['id']) || !isset($body['columnIdx']) || !is_int($body['columnIdx'])) {
    throw new Exception('Not found');
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
  throw new Exception('Invalid request method ' . $_SERVER['REQUEST_METHOD']);
}
