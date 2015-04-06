<?php

spl_autoload_register(function ($class) {
    include '../lib/Process/' . $class . '.php';
});

$start = filter_input(INPUT_GET, 'start', FILTER_VALIDATE_BOOLEAN);
$stop = filter_input(INPUT_GET, 'stop', FILTER_VALIDATE_BOOLEAN);

$node_path = '/opt/local/bin/node';
if (!file_exists($node_path)) {
    echo json_encode(array('Error: node.js not found!'));
    exit();
}
$node_server_path = 'server.js > /dev/null &';

$process_info_all = shell_exec('ps -ax');

if ($start) {
    if (preg_match('/server.js/', $process_info_all)) {
        echo json_encode(array('Node Server already running!'));
        exit();
    }
    $process = new Process($node_path . ' ' . $node_server_path);
    $process->start();
    $pid = $process->getPid();
    $process_info_node = shell_exec('ps -p ' . ($pid + 2));

    $i = 0;
    do {
        if (preg_match('/server.js/', $process_info_node)) {
            echo json_encode(array(('Node server running (PID: ' . ($pid + 2) . ')')));
            exit();
        } else {
            echo json_encode(array('Node server not running, trying again...'));
        }
        sleep(5);
        $i++;
    } while ($i < 2);
} else if ($stop) {
    $process_array = explode("\n", $process_info_all);
    $matches = implode(' ', preg_grep('/server.js/', $process_array));
    if (empty($matches)) {
        echo json_encode(array('Node server already shut down.'));
        exit();
    }
    $pid = preg_match('/[0-9]+/', $matches, $match);
    shell_exec('kill ' . $match[0]);
    echo json_encode(array('Node server shut down (PID: ' . $match[0] . ')'));
} else {
    exit('nothing to see here...');
}


