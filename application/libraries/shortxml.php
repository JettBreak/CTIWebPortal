<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 *   ShortXML        ShortXML implementation in PHP
 *   Copyright (c)   2005-2008 Mar Angelo S. Valencia <msv@corewaretech.com>
 *                   Coreware Tecnologies, Inc.
 *                   All Rights Reserved
 *
 *   Version:        1.01
 *   Date:           2006.11.30
 *
 *   Author:         Mar Angelo S. Valencia <msv@corewaretech.com>
 *
 *   This program is proprietary  software; you can redistribute it
 *   and/or modify it under the terms of the Coreware Technologies, Inc.
 *
*/
class ShortXML {
  var $xmldata     ;
  
  function __construct() {
    $this->xmldata = "" ; 
  }

  function setXML( $xml ) { 
    $this->xmldata = $xml ;
  }

  function getXML( ) { 
    return $this->xmldata ;
  }

  function isTagExist( $tag ) {
    $stag = "<" . $tag . ">";
    $result = strstr($this->xmldata,$stag);
    if ($result == false) {
      return false;
    } else {
      return true;
    }
  }

  function getValue( $tag ) {
    $stag = "<" . $tag . ">";
    $result = strstr($this->xmldata,$stag);
    if ( $result == false ) {
      $result = "";
    } else {
      $pos = strpos( $result, "</>" ) ; 
      if ( $pos == false ) {
        $result = "";
      } else {
        $result = substr($result, 0+strlen($stag),$pos - strlen($stag));
      } 
    }
    //echo "end result=[" . $result . "]\n"; 
    return $result;
  }

  function addTag( $tag, $value ) {
    $stag = "<" . $tag . ">";
    if ( $this->isTagExist( $tag ) ) {
       $pos = strpos($this->xmldata, $stag);
       $left = substr( $this->xmldata, 0, $pos);       
       $result = strstr($this->xmldata,$stag);
       $pos = strpos( $result, "</>" ) ; 
       $right = substr( $result, $pos+3, strlen($result)); 
       $this->xmldata =  $left . $stag . $value . "</>" . $right;
       return true;
    } else {
       $this->xmldata = $this->xmldata . $stag . $value . "</>";
       return true;
    } 
  }


  function editTag( $tag, $value ) {
     return $this->addTag( $tag, $value );
  }

}