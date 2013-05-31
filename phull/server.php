<?php

header('Content-type: application/json');
$response = array();


    $pid = empty($_REQUEST['pid']) ? uniqid() : $_REQUEST['pid'];
$timeout = empty($_REQUEST['to'])  ? 25       : $_REQUEST['to'];
     $op = empty($_REQUEST['op'])  ? 'pull'   : $_REQUEST['op'];
    $rev = empty($_REQUEST['rev']) ? 1        : $_REQUEST['rev'];


$db = new SQLite3('.phull');
$db->busyTimeout(500);
$db->exec('create table if not exists phull (pid text, acts text, rev integer default 1)');
$db->exec('create index if not exists pid on phull (pid)');

switch ($op)
{
    case 'connect':
        $stmt = $db->prepare("INSERT INTO phull (pid) VALUES (:pid)");
        $stmt->bindParam(':pid', $pid);
        $stmt->execute();

        $response[0] = $pid;
        $response[1] = $rev;

        echo json_encode($response);
    break;

    case 'emit':
        $request = file_get_contents('php://input');

        $stmt = $db->prepare("UPDATE phull SET acts = :acts, rev = rev + 1");
        $stmt->bindValue(':acts', $request);
        $stmt->execute();

        $response[] = $db->changes();

        echo json_encode($response);
    break;

    case 'pull':
        $stmt = $db->prepare("SELECT rev, acts FROM phull WHERE pid = :pid AND rev > :rev");
        $stmt->bindParam(':pid', $pid);
        $stmt->bindParam(':rev', $rev);

        $result = $stmt->execute();
        $set = $result->fetchArray(SQLITE3_NUM);

        while (empty($set))
        {
            if (time() > $_SERVER['REQUEST_TIME'] + $timeout)
            {
                header('HTTP/1.0 204 No Content', true, 204);
                exit;
            }

            usleep(250);

            $result = $stmt->execute();
            $set = $result->fetchArray(SQLITE3_NUM);
        }

        $response[0] = $set[0];
        $response[1] = json_decode($set[1]);

        echo json_encode($response);
    break;
}