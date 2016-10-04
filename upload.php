<?php

if($_FILES["userfile"]["error"] > 0)
{
    echo "Return codeqwe: " .$_FILES["userfile"]["error"]."<br/>"; 
}
else
{
$uploadFileName = $_FILES["userfile"]["name"];
    move_uploaded_file($_FILES["userfile"]["tmp_name"], 
        "./upload/".$uploadFileName);
}

$link = mysql_connect("localhost", "root", "") or die("Couldn't connect to db");
$db = mysql_select_db("b7_18949845_pox");

set_include_path(get_include_path() . PATH_SEPARATOR . 'Classes/');

include 'PHPExcel/IOFactory.php';

try
{
    $objPHPEcxel = PHPExcel_IOFactory::load("./upload/".$uploadFileName);
} catch (Exception $ex) 
{
    die('Error loading file "'.pathinfo($uploadFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
}

$allDataInSheet = $objPHPEcxel->getActiveSheet()->ToArray(null, true, true, true);
$sheet = $objPHPEcxel->getActiveSheet();
$columnCount = $objPHPEcxel->setActiveSheetIndex(0)->getHighestColumn();

$filledColumn = 0;
$columnCount = ord($columnCount);
$columnName = array();

$rowCount = count($allDataInSheet);
$rowData;

$f = 0;
$s = 0;

for($i = 65; $i <= $columnCount; $i++)
{
    $data = $sheet->getCell(chr($i)."1")->getCalculatedValue();
    if($data != null)
    {
        $columnName[] = $data;
        for($j = 2; $j <= $rowCount; $j++)
        {
            $data2 = $sheet->getCell(chr($i)."$j")->getCalculatedValue();
            $rowData[$f][$s] = $data2;
            $s++;
        }
    }
    $s = 0;
    $f++;
}

/*$tableName = substr($uploadFileName,0,strlen($uploadFileName) - 5);
$part1 = "create table ".$tableName
        ."( ";

for($i = 0; $i < count($columnName); $i++)
{
    if($i == count($columnName) - 1)
        $part1 .= $columnName[$i] ." text); ";
    else $part1 .= $columnName[$i] ." text, ";
}

$sql = mysql_query($part1);*/

$part2 = "insert into equipments"." (";
for($i = 0; $i < count($columnName); $i++)
{
    if($i == count($columnName) - 1)
        $part2 .= $columnName[$i] .") values(";
    else $part2 .= $columnName[$i] .", ";
}

$query = "";
for($i = 0; $i < $rowCount-1; $i++)
{
    for($j = 0; $j < count($columnName); $j++)
    {
        if($j == count($columnName) - 1)
            $query .= "\"".$rowData[$j][$i] ."\"); ";
        else $query .= "\"".$rowData[$j][$i] ."\", ";
    }
    $sql = mysql_query($part2.$query);
    //print_r($part2.$query);
    $query = "";
}
	echo '<a href='/print "<script type=\"text/javascript\">document.location.href =\"index.php\";</script>";'>';
?>