<?php
class Auth
{
	protected $gm;
	protected $pdo;


	public function __construct(\PDO $pdo)
	{
		$this->gm = new GlobalMethods($pdo);
		$this->pdo = $pdo;
	}


	// Headers 

	protected function generateHeader()
	{
		$h = [
			"typ" => "JWT",
			"alg" => 'HS256',
			"app" => "Sample Api",
			"dev" => "Loudel Manaloto"
		];
		return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($h)));
	}

	protected function generatePayload($uc, $ue, $ito)
	{
		$p = [
			'uc' => $uc,
			'ue' => $ue,
			'ito' => $ito,
			'iby' => 'Loudel Manaloto',
			'ie' => 'loudel.manaloto@gordoncollegeccs.edu.ph',
			'idate' => date_create()
		];
		return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($p)));
	}

	protected function generateToken($code, $course, $fullname)
	{
		$header = $this->generateHeader();
		$payload = $this->generatePayload($code, $course, $fullname);
		$signature = hash_hmac('sha256', "$header.$payload", "superpassword");
		return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
	}

	public function authorized() {

		$hdrs = apache_request_headers();
		$authHeader = '';
		$authUser = '';
		foreach ($hdrs as $header => $value) {
	    if($header == "Authorization") {
	    	$authHeader = $value;
	    }
	    if($header == "X-Auth-User") { 
	    	$authUser = $value;
	    }
		}
		$sql = "SELECT token FROM accounts_tbl WHERE empno='$authUser'";
		$res = $this->gm->execute_query($sql, "Incorrect username or password");
		if ($res['code'] == 200) {
			if($res['data'][0]['token']==$authHeader){
				return true;
			} else {
				return false;
			}
		} 
		return false;

	}


	//Password check

	public function encrypt_password($pword)
	{

		$hashFormat = "$2y$10$";

		$saltLength = 22;

		$salt = $this->generate_salt($saltLength);

		return crypt($pword, $hashFormat . $salt);
	}

	protected function generate_salt($len)
	{

		$urs = md5(uniqid(mt_rand(), true));

		$b64String = base64_encode($urs);

		$mb64String = str_replace('+', '.', $b64String);

		return substr($mb64String, 0, $len);
	}
	public function pword_check($pword, $existingHash)
	{

		$hash = crypt($pword, $existingHash);

		if ($hash === $existingHash) {

			return true;
		}
		return false;
	}

	public function login($data)
	{
		$username = $data->param1;
		$password = $data->param2;
		$sql = "SELECT * FROM accounts_tbl WHERE username = '$username' ";
		$res = $this->gm->execute_query($sql, 'Incorrect Username or Password');

		if ($res['code'] == 200) {
			if ($this->pword_check($password, $res['data'][0]['password'])) {
				$uc = $res['data'][0]['empno'];
				$ue = $res['data'][0]['email'];
				$fn = $res['data'][0]['fname'] . ' ' . $res['data'][0]['lname'];
				$tk = $this->generateToken($uc, $ue, $fn);

				$sql = "UPDATE accounts_tbl SET token='$tk' WHERE empno='$uc'";
				$this->gm->execute_query($sql, "");

				$code = 200;
				$remarks = "success";
				$message = "Logged in successfully";
				$payload = array("id" => $uc, "fullname" => $fn, "key" => $tk);
			}else {
				$payload = null; 
				$remarks = "failed"; 
				$message = "Incorrect password";
				
			}
		}else {
			$payload = null; 
			$remarks = "failed"; 
			$message = $res['errmsg'];		
		}

		return $this->gm->api_result($payload, $remarks, $message, $res['code']);
	}

	public function adduser($data){
		$data->password = $this->encrypt_password($data->password);
		$res = $this->gm->insert('accounts_tbl', $data);
		if($res['code']==200){
			$payload = null;
			$remarks = "success";
			$message = "succesfully registered";
		}
		else{
			$payload = null;
			$remarks = "failed";
			$message = "failed to register";
		}

		return $this->gm->api_result($payload, $remarks, $message, $res['code']);

	}
}
