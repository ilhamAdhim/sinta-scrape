<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>List lecturer</title>
</head>
<body>
    <h2>Lecturer MI </h2> <br>
    @foreach ($data['D3'] as $item)
        <?=$item?> <br>
    @endforeach

    <br><br>

    <h2>Lecturer TI </h2> <br>

    @foreach ($data['D4'] as $item)
        <?=$item?> <br>
    @endforeach
</body>
</html>