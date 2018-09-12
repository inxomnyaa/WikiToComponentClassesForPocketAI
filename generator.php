<?php

require_once "BaseClass.php";

$data = file_get_contents("data.txt");

$spliteach = "/=== [^ ]*+ ===/m";
$descpreg = "/;Description[\W]+: (\w[^\n]++)/m";

//TOPICS === mc:bla ===
$namespaces = null;
preg_match_all($spliteach, $data, $namespaces);

$namespaces1 = $namespaces[0];

//DESCS
$descriptions = null;
preg_match_all($descpreg, $data, $descriptions);

$descriptions1 = $descriptions[1];

//CONTENT BETWEEN TOPICS
$mainparts = preg_split($spliteach, $data);

foreach ($namespaces1 as $index => $name) {
    $currentpart = $mainparts[$index + 1];
    $currentpart = trim(preg_filter($descpreg, "", $currentpart));

    $name = str_replace("===", "", str_replace(" ", "", str_replace("''", "", $name)));
    $colonsplit = explode(":", $name)[count(explode(":", $name)) - 1];
    $classname = explode(".", $colonsplit)[count(explode(".", $colonsplit)) - 1];

    $replace = trim(str_replace(":", "\\", str_replace(".", "\\", $name)));
    $namespace = rtrim(str_replace($classname, "", "xenialdan\\PocketAI\\component\\" . $replace), "\\");

    $class = new BaseClass();
    $class->name = $name;
    $class->classname = "_" . $classname;
    $class->description = $descriptions1[$index];
    $class->namespace = $namespace;
    if (!empty($currentpart)) {
        $table = $currentpart;
        preg_match_all("/\{([^\]]*)\}/m", $table, $data);
        $splitdata = explode("|-", $data[1][0]);
        array_shift($splitdata);

        foreach ($splitdata as $paramlist) {
            $splitparam = explode("|", $paramlist);
            array_shift($splitparam);
//TODO IF [4] IS WIKITABLE SKIP OR STH LIKE THAT..
            $type = 'mixed';
            $default = trim($splitparam[2]);
            switch (trim($splitparam[0])) {
                case 'String':
                    $type = 'string';
                    if (!empty($default)) $default = '"' . $default . '"';
                    break;
                case 'Minecraft Filter':
                    $type = 'string (Minecraft Filter)';
                    if (!empty($default)) $default = '"' . $default . '"';
                    break;
                case 'Boolean':
                    $type = 'bool';
                    break;
                case 'target':
                    $type = 'mixed (target)';
                    if (!empty($default)) $default = '"' . $default . '"';
                    break;
                case 'List':
                    $type = 'array';
                    break;
                case 'Integer':
                    $type = 'int';
                    break;
                case 'Decimal':
                    $type = 'float';
                    break;
                case 'JSON Object':
                    $type = 'mixed (JSON Object)';
                    if (!empty($default)) $default = '"' . $default . '"';
                    break;
                case 'Positive Integer':
                    $type = 'int';
                    break;
                case 'Trigger':
                    $type = 'mixed (Trigger)';
                    if (!empty($default)) $default = '"' . $default . '"';
                    break;
            }
            $class->privates[trim($splitparam[1])] = ['type' => $type, 'desc' => trim($splitparam[3]), 'default' => $default];
        }
    } else {
        $class->constructsetters = [];
        $class->privates = [];
    }

    $filename = __DIR__ . DIRECTORY_SEPARATOR . "CLASSES" . DIRECTORY_SEPARATOR . $class->namespace . DIRECTORY_SEPARATOR . $class->classname . '.php';
    $dirname = pathinfo($filename, PATHINFO_DIRNAME);
    if (!file_exists($dirname)) {
        mkdir($dirname, 0777, true);
        chmod($dirname, 0777);
    }
    echo file_put_contents($filename, $class->generate());
}

