<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Parker</title>
</head>
<body>
    <div style="text-align: center">
        <img style="width:100px;" src="{{ $message->embed(base_path() . '/resources/assets/logo.png') }}" />
        <h1>{{$details['title']}}</h1>
        <p>{{$details['body']}}</p>
        <p style="font-size: 12px; color:gray;">Do not reply to this email</p>
    </div>

</body>
</html>

