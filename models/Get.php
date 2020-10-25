<?php
class Get
{
	protected $gm;

	public function __construct(\PDO $pdo)
	{
		$this->gm = new GlobalMethods($pdo);
    }
    
    public function getInfo($param){
        $sql = "SELECT * FROM employee_tbl";
        if($param!=null){
            $sql .= " WHERE recno = $param";
        }
       $res = $this->gm->execute_query($sql, "No data found");

       if($res['code']==200){
           $payload = $res['data'];
        //    $code = 200;
           $remarks = "success";
           $message = "Succesfully retrieved data";
       }
       else{
           $payload = null;
           $remarks = "Failed";
           $message = $res['errmsg'];
       }

       return $this->gm->api_result($payload, $remarks, $message, $res['code']);
    }

}
?>