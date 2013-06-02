<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Phull</title>
    <link href="//netdna.bootstrapcdn.com/bootswatch/2.3.1/cosmo/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="span12">
            <h1>Phull example</h1>
            <hr>
        </div>
    </div>
    <div class="row">
        <div class="span12">
            <form class="form-inline well" id="emit-form">
                <input type="text" id="your-name" placeholder="your name">
                <input type="text" id="your-text" placeholder="your text">
                <button type="submit" class="btn btn-primary">Send</button>
                <button type="reset" class="btn" id="disconnect-btn">Disconnect</button>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="span12">
            <ul class="unstyled" id="messages">
                <script id="message-template" type="text/x-handlebars">
                    <li>
                        <p>
                            <strong><i class="icon icon-user"></i>{{user}} says: </strong>
                            {{text}}<br>
                            <small>
                                <time class="update" datetime="{{at}}"></time>
                            </small>
                        </p>
                    </li>
                    <hr>
                </script>
            </ul>
        </div>
    </div>
</div>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.1/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.0.0/moment.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/1.0.0-rc.4/handlebars.min.js"></script>
<script src="phull/client.js"></script>
<script>
    phull.connect('phull/', function()
    {
        phull.emit({user: 'user' + Math.round(Math.random() * 100), text: 'hello world', at: new Date});
    },
    function (message)
    {
        var source = $("#message-template").html(),
            template = Handlebars.compile(source),
            html = template(message);

        $('#messages').prepend(html);
    });

    $('#emit-form').on('submit', function (event)
    {
        phull.emit({
            user: $('#your-name').val(),
            text: $('#your-text').val(),
            at: new Date
        });

        $('#your-text').val('');

        event.preventDefault();
    });

    $('#disconnect-btn').on('click', function (event)
    {
        phull.disconnect();
    });

    setInterval(function()
    {
        $('time.update').each(function()
        {
            $(this).text(moment($(this).attr('datetime')).fromNow());
        });
    }, 1000);
</script>
</body>
</html>