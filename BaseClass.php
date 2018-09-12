<?php


class BaseClass
{

    public $classname = "";
    public $namespace = "";
    public $name = "";
    public $privates = [];
    public $description = "";
    public $constructsetters = [];

    public function generate(){
        $file = file_get_contents("template.txt");
        $file = str_replace("%CLASSNAME%", $this->classname, $file);
        $file = str_replace("%NAMESPACE%", $this->namespace, $file);
        $file = str_replace("%DESCRIPTION%", $this->description, $file);
        $file = str_replace("%NAME%", $this->name, $file);
        $privates = "";
        $constructsetters = "";
        foreach ($this->privates as $privatename => $privatesub){
            $privates .= "/** @var ".$privatesub['type']." $".$privatename." ".$privatesub['desc']." */
            public $".$privatename;
            if(!is_null($privatesub['default']) && !empty(trim($privatesub['default']))){
                $privates .= " = " . $privatesub['default'];
            }
            $privates .= ";
            ";


            $constructsetters .= "\$this->" . $privatename . " = \$values['".$privatename."']??\$this->" . $privatename . ";
            ";
        }
        $file = str_replace("%PRIVARIABLES%", $privates, $file);
        $file = str_replace("%CONSTRUCTSETTERS%", $constructsetters, $file);
        return $file;
    }
}