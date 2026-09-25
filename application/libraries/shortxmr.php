<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 *   ShortXMR        ShortXMR implementation in PHP
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
class ShortXMR {
  var $xmrdata     ;
  
  function __construct() {
    $this->xmrdata = "" ; 
  }

  function setXMR( $xmr ) { 
    $this->xmrdata = $xmr ;
  }

  function getXMR( ) { 
    return $this->xmrdata ;
  }

  function isTagExist( $tag ) {
    $stag = "[" . $tag . "]";
    $result = strstr($this->xmrdata,$stag);
    if ($result == false) {
      return false;
    } else {
      return true;
    }
  }

  function getValue( $tag ) {
    $stag = "[" . $tag . "]";
    $result = strstr($this->xmrdata,$stag);
    if ( $result == false ) {
      $result = "";
    } else {
      $pos = strpos( $result, "[/]" ) ; 
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
    $stag = "[" . $tag . "]";
    if ( $this->isTagExist( $tag ) ) {
       $pos = strpos($this->xmrdata, $stag);
       $left = substr( $this->xmrdata, 0, $pos);       
       $result = strstr($this->xmrdata,$stag);
       $pos = strpos( $result, "[/]" ) ; 
       $right = substr( $result, $pos+3, strlen($result)); 
       $this->xmrdata =  $left . $stag . $value . "[/]" . $right;
       return true;
    } else {
       $this->xmrdata = $this->xmrdata . $stag . $value . "[/]";
       return true;
    } 
  }


  function editTag( $tag, $value ) {
     return $this->addTag( $tag, $value );
  }

}
?>
