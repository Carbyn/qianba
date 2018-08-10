<?php
class AbstractModel {

    public $db;
    
    public function __construct() {
        $this->db = \Explorer\DB::getInstance();
    }

}
