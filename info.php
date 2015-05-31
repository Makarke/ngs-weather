<?php
    error_reporting(E_ALL);
    header("Content-Type: text/html; charset=utf-8");
    include 'db.php';
    include 'func.php';
    $db = new DB('localhost','test');
    define('FOR_DAYS',3);
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="style.css" />
    </head>
    <body>
    <?php
        if (isset($_GET['id'])){
            try {
                $tempid = new MongoId($_GET['id']);
            } catch (MongoException $ex) {
                echo $ex;
                exit();
            }
        } else {
            echo 'Недопустимый параметр';
            exit();
        }
        $lastcity="";
        $lastalias="";
        $collection = $db->getCollection('mycity');
        if (!$db->isCollectionEmpty($collection)) {
            $cursor = $collection->findOne(array('_id' => $tempid));
            if (count($cursor) != 0)
            {
                $my_col = $db->getCollection('lastcity');
                $newcity = array('$set' =>
                    array("cityalias" => $cursor['cityalias'],"cityname" => $cursor['cityname']));
                $my_col->update(array(),$newcity);
                $lastcity = $cursor['cityname'];
                $lastalias = $cursor['cityalias'];
            } else {
                echo 'Недопустимый параметр';
                exit();
            }
            
        }
        $weather = getWeatherInCity($lastalias);
        $forecast = getForecastInCity($lastalias,FOR_DAYS);
    ?>
    <div id="content">
        <div id="top">
            <?php echo $lastcity;?>
            <br>
            Температура:<?php echo $weather['temperature']?>&degС 
            Давление:<?php echo $weather['pressure']?>мм рт.ст. 
            Влажность:<?php echo $weather['humidity']?>% 
        </div>
        <h1><?php echo $lastcity;?></h1>
        <table id="cities">
        <tr>
            <td>Прогноз погоды на 3 дня<br>
            <table>
                <tr>
                    <td>Дата</td>
                    <td>Температура</td>
                    <td>Влажность</td>
                </tr>
                <?php
                    for ($i = 0; $i < FOR_DAYS; $i++)
                    {
                        $date = date('d.m.Y', strtotime($forecast[$i]['date']));?>
                        <tr>
                            <td><?php echo $date; ?></td>
                            <td><?php echo $forecast[$i]['temperature']; ?>&degС</td>
                            <td><?php echo $forecast[$i]['humidity']; ?>%</td>
                        </tr>
                        <?php
                    }
                ?>
            </table>
        </td>
        <td>Архив погоды<br>
            <table>
                <tr>
                    <td>Дата</td>
                    <td>Температура</td>
                    <td>Влажность</td>
                </tr>
                <?php
                    $archive_col = $db->getCollection('archive');
                    if (!$db->isCollectionEmpty($archive_col)) {
                        $cursor = $archive_col->find(array('cityid' => $tempid));
                        $cursor->sort(array('_id' => -1));
                        $cursor->limit(FOR_DAYS);
                        foreach ($cursor as $city_doc) {?>
                            <tr>
                                <td><?php echo $city_doc['updtime']; ?></td>
                                <td><?php echo $city_doc['temperature']; ?>&degС</td>
                                <td><?php echo $city_doc['humidity']; ?>%</td>
                            </tr>
                        <?php
                        }
                    }
                ?>
            </table>
        </td>
    </tr>
    </table>
    <form action="update.php">
        <button type="submit">Обновить</button>
    </form>
    </body>
    <?php
        $db->close();
        exit();
    ?>
</html>
