<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of general
 *
 * @author adrian
 */
class general {
    //put your code here

       
    public function toLog($error){
        
           $myfile = fopen("./lib/helper/error.log", "w") or die("Unable to open file!");
           fwrite($myfile, $error);
           fclose($myfile);
        
    }
  
    
}
