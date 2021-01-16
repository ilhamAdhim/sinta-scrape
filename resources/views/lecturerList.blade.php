<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>List lecturer</title>
    <body>
</head>
    <h2>Lecturer MI </h2> <br>

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
            </tr>
        </thead>
        <tbody>
            <?= 'User ID for MI Gathered : '.count($data['D3']['link']) ?>
            <tr>
                @foreach ($data['D3']['link'] as $item)
                <td>
                    <?=$item?>
                </td>
                @endforeach
            </tr>
        </tbody>
    </table>


    <br><br>

    <h2>Lecturer TI </h2> <br>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
            </tr>
        </thead>
        <tbody>
            <?= 'User ID for TI Gathered : '.count($data['D4']['link']) ?>
            <tr>
                @foreach ($data['D4']['link'] as $item)
                <td>
                    <?=$item?>
                </td>
                @endforeach
            </tr>
        </tbody>
    </table>

</body>
</html>