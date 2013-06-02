# Phull

Client-server communication between JS and PHP via Ajax long polling.

## Usage
###### Get real-time behavior in 3 simple steps.

#### #1 - Put it in your server
Drop `phull` folder into your webserver accessible folder.

#### #2 - Load de client API
Link the client-side code to your page like any other JavaScript

 `<script src="path/to/phull/client.js"></script>`

#### #3 - Very easy to use
Now just connect and set the listener for incoming messages:

    phull.connect('path/to/phull/', function()
    {
        phull.emit({user: 'user123', text: 'hello world', at: new Date});
    },
    function (message)
    {
        console.dir(message);
    });

Take a look at `example.php` for a simple chat example.

[See it in action!](http://leocavalcante.com/projects/phull/example.php)