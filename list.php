<?php
    error_reporting(E_ALL);
    header("Content-Type: text/html; charset=utf-8");
    include 'db.php';
    include 'func.php';
    $db = new DB('localhost','test');
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="style.css" />
    </head>
    <body>
    <?php
        $collection = $db->getCollection('cities');
        if ($db->isCollectionEmpty($collection)) {
            insertCitiesList($collection);
        }
        $lastcity="";
        $lastalias="";
        $my_col = $db->getCollection('lastcity');
        if ($db->isCollectionEmpty($my_col)) {
            $city_cursor = $collection->find()->limit(1);
            foreach ($city_cursor as $city_doc) {
                $lastcity = $city_doc['title'];
                $lastalias = $city_doc['alias'];
                $city = array("cityname" => $lastcity, "cityalias" => $lastalias);
                $my_col->insert($city);
            }
        } else {
            $city_cursor = $my_col->findOne();
            $lastcity = $city_cursor['cityname'];
            $lastalias = $city_cursor['cityalias'];
        }
        if (isset($_POST['add'])) {
            $city_cursor = $collection->findOne(array('alias' => $_POST['citieslist']));
            $fav_col = $db->getCollection('mycity');
            $city = array("cityname" => $city_cursor['title'],
                "cityalias" => $city_cursor['alias']);
            $fav_col->insert($city);
        }
        $weather = getWeatherInCity($lastalias);
    ?>
    <div id="content">
        <div id="top">         
            <?php echo $lastcity;?>
            <br>
            Температура:<?php echo $weather['temperature']?>&degС 
            Давление:<?php echo $weather['pressure']?>мм рт.ст. 
            Влажность:<?php echo $weather['humidity']?>%
        </div>
        <form action="list.php" method="post">
            <button type="submit" name="add">Добавить город</button>
            <select name="citieslist">
            <?php
                $collection = $db->getCollection('cities');
                $cursor = $collection->find(array(),array('title','alias'));
                foreach ($cursor as $doc) {?>
                    <option value=<?php echo $doc['alias']; ?>><?php echo $doc['title']; ?></option>
                <?php
                }
            ?>
            </select>
            <?php
                if (isset($_POST['del']) || isset($_POST['save']))
                {
                    $city_col = $db->getCollection('mycity');
                    $city_cursor = $city_col->find();
                    $i=1;
                    foreach ($city_cursor as $city_doc) {
                        if (isset($_POST['del']) && $_POST['del'] == $i){
                            $city_col->remove(array('_id' => new MongoId($city_doc['_id'])));
                        }
                        if (isset($_POST['save'])){
                            $cursor = $collection->find(array('alias' => $_POST['textalias']));
                            if ($cursor->count() > 0 && $_POST['save'] == $i) {
                                $newdata = array('$set' =>
                                array("cityalias" => $_POST['textalias'],"cityname" => $_POST['texttitle']));
                                $city_col->update(array('_id' => new MongoId($city_doc['_id'])),$newdata);
                            }
                        }
                        $i++;
                    }
                }
            ?>
            <table cellpadding="5" width="100%" id="cities">
            <tr>
                <td>№</td>
                <td>Город</td>
                <td></td>
                <td></td>
            </tr>
            <?php
                $city_col = $db->getCollection('mycity');
                if ($db->isCollectionEmpty($city_col) == false){
                    $city_cursor = $city_col->find();
                    $i=1;
                    foreach ($city_cursor as $city_doc) {?>
                        <tr>
                        <td><?php echo $i; ?></td>
                        <?php
                        if (isset($_POST['edit'])){
                            if ($_POST['edit'] == $i) {?>
                                <td>
                                    <input type="text" name="texttitle" value="<?php echo $city_doc['cityname']; ?>">
                                    <input type="text" name="textalias" value="<?php echo $city_doc['cityalias']; ?>">
                                </td>
                                <td>
                                    <button type="submit" name="save" value="<?php echo $i; ?>">Сохранить</button>
                                </td>
                            <?php
                            } else {
                            ?>
                                <td>
                                    <a href="info.php">
                                        <?php echo $city_doc['cityname']; ?>
                                    </a>
                                </td>
                                <td>
                                    <button type="submit" name="edit" value="<?php echo $i; ?>">Изменить</button>
                                </td>
                            <?php
                            }
                        } else {
                            ?>
                            <td>
                                <a href="info.php?id=<?php echo $city_doc['_id']; ?>">
                                    <?php echo $city_doc['cityname']; ?>
                                </a>
                            </td>
                            <td>
                                <button type="submit" name="edit" value="<?php echo $i; ?>">Изменить</button>
                            </td>
                        <?php
                        }
                        ?>
                        <td>
                            <button type="submit" name="del" value="<?php echo $i; ?>">Удалить</button>
                        </td>
                        </tr>
                    <?php
                        $i++;
                    }
                }
            ?>
            </table>
        </form>
    </div>
    </body>
    <?php
        $db->close();
        exit();
    ?>
</html>    
