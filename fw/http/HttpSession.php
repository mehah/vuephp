<?php
namespace http\HttpSession;

use fw\Core;

class HttpSession
{

    private $attr = Array();

    public function __construct()
    {}

    public function getAttribute($index)
    {
        return $this->attr[$index] ?? null;
    }

    public function setAttribute($index, $value)
    {
        $this->attr[$index] = $value;
    }

    public function destroy()
    {
        unset($_SESSION[Core::$PROJECT_NAME]);
    }
}