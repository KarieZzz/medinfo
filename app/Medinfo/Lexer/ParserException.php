<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 06.11.2016
 * Time: 17:46
 */

namespace App\Medinfo\Lexer;

use Exception;

class ParserException extends Exception
{

    private $error_code;

    public function __construct($message, int $error_code = 0)
    {
        parent::__construct($message);
        $this->error_code = $error_code;
    }

    public function getErrorCode()
    {
        return $this->error_code;
    }

}