<?php
/*
 *   CoreAS.php      Class integration to Core AS (Advanced Server)
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
class CoreAS {
  var $host   ;
  var $port   ; 
  var $socket ;
  var $errno  ;
  var $errstr ;
  var $TIMEOUT;
  var $buf    ;
  var $path   ; 
  var $data   ;
  
  //CONSTRUCTOR default timeout to 20secs
  function __construct( $params ) {
    $this->host   = $params['host'];
    $this->port   = $params['port'];
    $this->timeout = 3 ; 
  }

  function connect() { 
    $this->socket = fsockopen( $this->host, $this->port, $this->errno, 
      $this->errstr, $this->timeout ); 
    if ( !$this->socket ) return false ; 
    else                  return true  ; 
  }

  function setXML( $xml ) {
    $this->data = $xml ;
  }

  function send() {
    if ( !$this->connect()) { 
      return false ; 
    } else {
      $this->buf = "" ;  
      fwrite( $this->socket, $this->data . "\n");
      //@echo "getting data--";
      while ( !feof($this->socket) ) {
        $this->buf .= fgets( $this->socket, 2048 ) ; 
        //@debug: echo "CoreAS::send::buf is -> " . $this->buf;
        if ($this->buf != "") break;
      }
      //@echo $this->buf ;
      $this->close() ; 
      return true ; 
    }
  }

  function getXML() {
    return $this->buf ; 
  }

  function close() {
    fclose( $this->socket ) ; 
  }

}


?>
