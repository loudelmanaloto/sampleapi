<?php
class Post
{
	protected $gm;
	protected $pdo;
	protected $get;

	public function __construct(\PDO $pdo)
	{
		$this->pdo = $pdo;
        $this->get = new Get($pdo);
        $this->gm = new GlobalMethods($pdo);
    }
    
    public function addemployee($data){
        $fields=[]; $values=[];
        if($data!=null){
            foreach($data as $key => $value){
                array_push($fields, $key);
                array_push($values, $value);
            }
        }
     
        try{
            $sql = "INSERT INTO employee_tbl(fname, mname, lname) VALUES (?,?,?)";
            $sql = $this->pdo->prepare($sql);
            $sql->execute($values);            
            $remarks = 'sucess';
            $message = 'sucessfully inserted';
            $code = 200;
              
        }
        catch(\PDOException $e){
            $message = $e->getMessage();
            $code = 403;
            $remarks = 'Failed';
            $payload = null;
        }
        if($code==200){
            return $this->get->getInfo(null);
        }

        return $this->gm->api_result($payload, $remarks, $message, $code);
        
    }

    public function editemployee($data, $recno){
        $values = []; $fields = [];
        if($data!=null){
            foreach($data as $key => $value){
                array_push($values, $value);
            }
        }

        try{
            $sql = "UPDATE employee_tbl SET fname = ?, mname = ?, lname = ? WHERE recno=$recno";
            $sql = $this->pdo->prepare($sql);
            $sql->execute($values);
            $code = 200;            
        }
        catch(\PDOException $e){
            $payload = null;
            $remarks = "Failed";
            $message = $e->getMessage();
            $code = 403;
        }
        if($code==200){
            return $this->get->getInfo($recno);
        }
        return $this->gm->api_result($payload, $remarks, $message, $code);
    }

    public function delete($param){
        
        try{
            $sql = "DELETE FROM `employee_tbl` WHERE `recno`=?";
            $sql = $this->pdo->prepare($sql);
            $sql->execute([$param]);
            $remarks = "Success";
            $message = "Successfully deleted record";
            $code = 200;
        }
        catch(\PDOException $e){
            $message = $e->getMessage();
            $code = 403;
            $payload = null;
            $remarks = "Failed";
        }
        
        if($code==200){
            return $this->get->getInfo(null);
        }

        return $this->gm->execute_query($payload, $remarks, $message, $code);
    }

    public function update($data, $param){
        $res = $this->gm->update('employee_tbl', $data, "recno='$param'");
        if($res['code']==200){
          return $this->get->getInfo($param);
        }
        else{
            $payload = null;
			$remarks = "failed";
			$message = $res['errmsg'];
        }
        return $this->gm->api_result($payload, $remarks, $message, $res['code']);
    }

    public function insert($data){
        $res = $this->gm->insert('employee_tbl', $data);

        if($res['code']==200){
            return $this->get->getInfo(null);
        }
        else{
            $payload = null;
			$remarks = "failed";
			$message = $res['errmsg'];
        }
        return $this->gm->api_result($payload, $remarks, $message, $res['code']);
    }


}
?>