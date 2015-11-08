<?php

class Mssql
{
  private $conn = null;

  public function __construct()
  {
    $this->connect();
  }
  
  public function connect()
  {
    try
    {
      $this->conn = new PDO( "sqlsrv:server=sivsql002.ir.intel.com; Database=dbn_evacation", "evac_dev", "absence");
      $this->conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    }
    catch(Exception $e)
    { 
      $this->conn = null; 
    }
  }
  
  public function disconnect()
  {
    try
    {
      unset( $this->conn );
      $this->conn = null;
    }
    catch(Exception $e)
    { 
      $this->conn = null; 
    }
  }
  
  public function is_connected()
  {
    return $this->conn == null ? false : true;
  }
  
  public function query( $sql )
  {
    return $this->conn->query( $sql );
  }
}