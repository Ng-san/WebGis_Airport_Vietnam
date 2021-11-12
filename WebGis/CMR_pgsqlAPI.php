<?php
$paPDO = initDB();
$paSRID = '4326';
if (isset($_POST['functionname'])) {
    $paPoint = $_POST['paPoint'];

    $functionname = $_POST['functionname'];

    $aResult = "null";

    if ($functionname == 'getInfoLocationToAjax')
        $aResult = getInfoLocationToAjax($paPDO, $paSRID, $paPoint);
    else if ($functionname == 'getGeoEagleToAjax')
        $aResult = getGeoEagleToAjax($paPDO, $paSRID, $paPoint);

    echo $aResult;

    closeDB($paPDO);
}
if (isset($_POST['search'])) {
    $search = $_POST['search'];
    $functionsearch = $_POST['functionsearch'];
    $aResult = "null";

    if ($functionsearch == 'searchInforAirport') {
        $aResult = searchInforAirport($paPDO, $paSRID, $search);
    }
    if ($functionsearch == 'searchIndexAirport') {
        $aResult = searchIndexAirport($paPDO, $paSRID, $search);
    }
    echo $aResult;
    closeDB($paPDO);

}

function initDB()
{
    // Kết nối CSDL

    $paPDO = new PDO('pgsql:host=localhost;dbname=MyDatabase;port=5432', 'postgres', 'san66719300');

    return $paPDO;
}
function query($paPDO, $paSQLStr)
{
    try {
        // Khai báo exception
        $paPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Sử đụng Prepare
        $stmt = $paPDO->prepare($paSQLStr);
        // Thực thi câu truy vấn
        $stmt->execute();

        // Khai báo fetch kiểu mảng kết hợp
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        // Lấy danh sách kết quả
        $paResult = $stmt->fetchAll();
        // echo "Success: " ;
        return $paResult;
    } catch (PDOException $e) {
        echo "Thất bại, Lỗi: " . $e->getMessage();
        return null;
    }
}
function closeDB($paPDO)
{
    // Ngắt kết nối
    $paPDO = null;
}


function getGeoEagleToAjax($paPDO, $paSRID, $paPoint)
{

    $paPoint = str_replace(',', ' ', $paPoint);
    $strDistance ="ST_Distance('".$paPoint."',ST_AsText(geom))";
    $strMinDistance="SELECT min(".$strDistance.") from \"airport\"";
    $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"airport\" where ".$strDistance."=(".$strMinDistance.") and ".$strDistance." < 0.1";
    // $mySQLStr = "SELECT * from \"airport\" where ST_Within('SRID=".$paSRID.";".$paPoint."'::geometry,geom)";
    $result = query($paPDO, $mySQLStr);
    
    if ($result != null)
    {
        // Lặp kết quả
        foreach ($result as $item){
            // echo($item['geo']);
            return $item['geo'];
            
        }
        // $string= json_encode($result[0]);
        // $string1=str_replace('"geo":"', '', $string);
        // $string2=str_replace('{', '', $string1);
        // return $string;
        // return $result['geo'].ToString();
    }
    else
        return "null";
}


//Hien thi thong tin dia diem
function getInfoLocationToAjax($paPDO, $paSRID, $paPoint)
{
    $paPoint = str_replace(',', ' ', $paPoint);
    $strDistance = "ST_Distance('" . $paPoint . "',ST_AsText(geom))";
    $strMinDistance = "SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom))) from airport";
    $mySQLStr = "SELECT * from airport where " . $strDistance . " = (" . $strMinDistance . ") and " . $strDistance . " < 0.1";


    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        $resFin = '<table>';
        // Lặp kết quả
        foreach ($result as $item) {
            foreach ($result as $item){
                $resFin = $resFin.'<tr><td>id: '.$item['id'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Tên sân bay: '.$item['name'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Kinh độ: '.$item['lat_gra'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Vĩ độ: '.$item['long_gra'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Loại: '.$item['type'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Thành Phố: '.$item['city'].'</td></tr>';
                break;
            }
            break;
        }
        $resFin = $resFin . '</table>';
        return $resFin;
    } else
        return "null";
}

function searchInforAirport($paPDO, $paSRID, $search)
{
    $mySQLStr = "SELECT * from \"airport\" where name like '$search'";
    $result = query($paPDO, $mySQLStr);
    if ($result != null) {
        $resFin = '<table>';
        // Lặp kết quả
        foreach ($result as $item) {
            foreach ($result as $item){
                $resFin = $resFin.'<tr><td>id: '.$item['id'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Tên sân bay: '.$item['name'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Kinh độ: '.$item['lat_gra'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Vĩ độ: '.$item['long_gra'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Loại: '.$item['type'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Thành Phố: '.$item['city'].'</td></tr>';
                break;
            }
            break;
        }
        $resFin = $resFin . '</table>';
        return $resFin;
    } else
        return "null";
}
function searchIndexAirport($paPDO, $paSRID, $search)
{
    $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"airport\" where name like '$search'";
    $result = query($paPDO, $mySQLStr);
    if ($result != null)
    {
        // Lặp kết quả
        foreach ($result as $item){
            // echo($item['geo']);
            return $item['geo'];
        }
    }
    else
        return "null";
}

?>
