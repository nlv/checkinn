<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Проверка достоверности ИНН</title>
</head>
<body>

    <h1>Проверка достоверности ИНН</h1>

    <form action="" method="post">
        <label for="#inn">Укажите ИНН: </label>
        <input type="text" id="inn" name="inn" value="<?= isset($_POST['inn']) ? $_POST['inn'] : ''?>"/>

        <input type="submit" value="Проветить"/>
    </form>

    <?php 
        if (isset($_POST['inn'])){
            include_once ('authenticity.php');
            $authenticity = new Authenticity();
            echo '<p>Проверка ИНН=' . $_POST['inn'] . ':</p><pre>';
            print_r($authenticity->get($_POST['inn']));
            echo '</pre>';
        }
    ?>


</body>
</html>
