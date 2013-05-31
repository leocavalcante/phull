# Phull

Client-server communication between JS and PHP via Ajax long polling.

## Usage
Drop `phull` folder into your webserver acessible folder.

Link the client-side code to your page like any other JavaScript

`<script src="path/to/phull/client.js"></script>`

Now just connect and set the listener for incoming messages.

    phull.connect('path/to/phull/', function()
    {
        phull.emit({user: 'user123', text: 'hello world', at: new Date});
    },
    function (message)
    {
        console.dir(message);
    });

Take a look at `index.php` for a simple chat example.