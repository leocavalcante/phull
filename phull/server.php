<?php

class Phull {

    private $pathname;
    private $filename;
    private $lockFilename;
    private $data;
    private $response;

    public function __construct()
    {
        $this->pathname = '.phull/';

        if ( ! is_dir($this->pathname))
        {
            mkdir($this->pathname);
        }

        $this->filename = $this->pathname.'storage';
        $this->lockFilename = $this->pathname.'lock';
        $this->data = new stdClass;
        $this->response = array();

        if ( ! is_file($this->filename))
        {
            $this->data->clients = new stdClass;
            $this->write();
        }
    }

    public function connect($pid = null)
    {
        $this->read();

        if (is_null($pid))
        {
            $pid = uniqid();
        }

        $client = new stdClass;
        $client->message = '';
        $client->rev = 1;

        $this->data->clients->{$pid} = $client;

        $this->write();

        $this->response[] = $pid;
        $this->response[] = 1;
    }

    public function emit($message)
    {
        $this->read();

        foreach ($this->data->clients as $pid => $client)
        {
            $client = $this->data->clients->{$pid};
            $client->message = $message;
            $client->rev += 1;
        }

        $this->write();

        header('HTTP/1.0 204 No Content', true, 204);
        exit;
    }

    public function pull($pid, $rev, $timeout = 25)
    {
        $this->read();

        $client = $this->data->clients->{$pid};
        $now = time();

        while ($client->rev <= intval($rev))
        {
            if (time() > $now + 25)
            {
                header('HTTP/1.0 204 No Content', true, 204);
                exit;
            }

            usleep(250);

            $this->read();

            $client = $this->data->clients->{$pid};
        }

        $this->response[] = $client->rev;
        $this->response[] = stripslashes($client->message);
    }

    public function getResponse()
    {
        return json_encode($this->response);
    }

    private function lock()
    {
        return file_put_contents($this->lockFilename, $this->lockFilename);
    }

    private function unlock()
    {
        return @unlink($this->lockFilename);
    }

    private function isLocked()
    {
        clearstatcache();
        return is_file($this->lockFilename);
    }

    private function read()
    {
        while ($this->isLocked())
        {
            usleep(1);
        }

        $this->lock();
        $this->data = json_decode(file_get_contents($this->filename));
        $this->unlock();
    }

    private function write()
    {
        while ($this->isLocked())
        {
            usleep(1);
        }

        $this->lock();
        file_put_contents($this->filename, json_encode($this->data));
        $this->unlock();
    }
}

$phull = new Phull;

switch ($_REQUEST['op']) {
    case 'connect':
        $phull->connect();
    break;

    case 'emit':
        $phull->emit(file_get_contents('php://input'));
    break;

    case 'pull':
        $phull->pull($_REQUEST['pid'], $_REQUEST['rev']);
    break;
}

header('Content-type: application/json');
echo $phull->getResponse();