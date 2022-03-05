<?php
namespace App\Validation;

class Errors {
    public static function getAll():array {
        return $_SESSION['errors']  ?? [];
    }
}