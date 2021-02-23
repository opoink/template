<?php
/**
* Copyright 2021 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Opoink\Template;

class Name {

    /**
     * convert the file name into pascal case letters
     */
    public function pascalCase($name){
        $name = str_replace(' ', '', $name);
        $name = $this->toUcFirst(explode('-', $name));
        $name = implode('', $name);
        $name = $this->toUcFirst(explode('_', $name));
        $name = implode('', $name);
        return $name;
    }

    /**
     * make all value of array to ucfirst
     * @param $name array
     */
    public function toUcFirst($name){
        foreach ($name as $key => $value) {
            $name[$key] = ucfirst($value);
        }
        return $name;
    }

    public function checkIfExist($target, $name, $ext){
        $name = $this->pascalCase($name);
        $_target = $target . DS . $name . DS . $name;
        $_target .= '.' . $ext;

        return file_exists($_target);
    }
}
?>