<?php
class User{
    //database connection and table name
    private $conn;   
 
    //object properties
    public $User_Name;
    public $Last_Name;
    public $First_Name;
    public $Middle_Name;
    public $Gender;
    public $User_Type_Id;
    public $Email_Address;
    public $Password;
    
    
   // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    // check if given email exist in the database
function userExist(){
 
    // query to check if email exists
    $query = "SELECT *  FROM tbluser WHERE User_Name=? AND Password=?";
 
    // prepare the query
    $stmt = $this->conn->prepare( $query );
 
    // sanitize
    $this->User_Name=htmlspecialchars(strip_tags($this->User_Name));
    $this->Password=htmlspecialchars(strip_tags($this->Password));
 
    // bind given email value
    $stmt->bindParam(1, $this->User_Name);
    $stmt->bindParam(2, $this->Password);
 
    // execute the query
    $stmt->execute();
 
    // get number of rows
    $num = $stmt->rowCount();
 
    // if email exists, assign values to object properties for easy access and use for php sessions
    if($num>0){
 
        // get record details / values
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
        // assign values to object properties
        $this->User_Name = $row['User_Name'];
        $this->First_Name = $row['First_Name'];
        $this->Last_Name = $row['Last_Name'];
        $this->Middle_Name = $row['Middle_Name'];
        $this->Gender = $row['Gender'];
        $this->User_Type_Id = $row['User_Type_Id'];
        $this->Email_Address = $row['Email_Address'];
        $this->Password = $row['Password'];
 
        // return true because user and password exists in the database
        return true;
    }
 
    // return false if user and password does not exist in the database
    return false;
}
 
}
?>
