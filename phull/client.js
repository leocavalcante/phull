var phull = phull || (function(){

    var pid,
        rev,
        serverPath,
        onconnected,
        listener,
        polling = false,
        connxhr = new XMLHttpRequest,
        pullxhr = new XMLHttpRequest,
        emitxhr = new XMLHttpRequest;

    connxhr.onreadystatechange = function(event)
    {
        if (connxhr.readyState === 4)
        {
            var res = JSON.parse(event.target.response);

            pid = res[0];
            rev = res[1];

            polling = true;

            pull();
            onconnected();
        }
    }

    pullxhr.onreadystatechange = function(event)
    {
        if (pullxhr.readyState === 4)
        {
            if (pullxhr.status === 200)
            {
                var res = JSON.parse(event.target.response);

                rev = res[0];
                res[1] && listener(JSON.parse(res[1]));
            }

            pull();
        }
    }

    function connect(path, connFn, fn)
    {
        serverPath = path;
        onconnected = connFn;
        listener = fn;

        connxhr.open('GET', getOpUrl('connect'));
        connxhr.send();
    }

    function disconnect()
    {
        polling = false;
    }

    function emit(data)
    {
        if (data)
        {
            emitxhr.open('POST', getOpUrl('emit'));
            emitxhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            emitxhr.send('data=' + JSON.stringify(data));
        }
    }

    function pull()
    {
        polling && !pullxhr.open('GET', getPullUrl()) && pullxhr.send();
    }

    function getOpUrl(operation)
    {
        return serverPath + operation;
    }

    function getPullUrl()
    {
        return serverPath + pid + '/' + rev;
    }

    return {
        connect: connect,
        disconnect: disconnect,
        emit: emit
    };
}());