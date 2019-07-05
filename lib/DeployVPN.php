<?php
	
/**
 * DeployVPN.php
 *
 * Simple library to integrate DeployVPN API
 *
 * @author     Sparkridge BVBA
 * @copyright  2019 Sparkridge BVBA
 * @license    https://www.deployvpn.com
 * @version    1.0.0
 * @link       https://docs.deployvpn.com/
 */
 
class DeployVPN
{	
	private $apiUrl;
	private $publicKey;
	private $privateKey;
	
    function __construct($apiUrl, $publicKey, $privateKey) {
       $this->apiUrl = $apiUrl;
       $this->publicKey = $publicKey;
       $this->privateKey = $privateKey;
    }	
    

    private function call($request_type, $request, $parameters, $isPrivate)
   	{
	   	if($isPrivate)
	   	{
		   	$token = $this->privateKey;
	   	}
	   	else
	   	{
		   	$token = $this->publicKey;
	   	}
	   	
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://".$this->apiUrl."/api/".$request,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => false,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => $request_type,
		  CURLOPT_POSTFIELDS => json_encode($parameters),
		  CURLOPT_HTTPHEADER => array(
		    "Content-Type: application/json",
		    "Authorization: Bearer " . $token
		  ),
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		
		curl_close($curl);
		
		if ($err) 
		{
		  return array("status" => false, "message" => "Could not connect to API");
		} 
		else 
		{
		  	$result = (array)json_decode($response);
			if (json_last_error() === JSON_ERROR_NONE) 
			{
			    return $result;
			}
			else
			{
				return array("status" => false, "message" => "Could not parse response from API."); 
			}		  
		}	   	
   	} 
   	
   	public function checkConnection()
   	{
	   	$result = $this->call("GET", "platform/testConnection/", array(), true);
	   	return $result;
   	}
   	
   	public function createUser($username, $password, $email, $bandwidth, $autorenew, $label, $language, $term)
   	{
	   
	   	$parameters = array("username" => $username, 
	   	"password" => $password,
	   	"email" => $email,
	   	"bandwidth" => $bandwidth,
	   	"autorenew" => $autorenew,
	   	"label" => $label,
	   	"language" => $language,
	   	"term" => $term);
	   	
	   	return $this->call("POST", "user/create/", $parameters, true);
   	}
   	
   	public function getUser($vpnuser_id)
   	{
	   	return $this->call("GET", "user/get/".$vpnuser_id."/", array(), true);
   	}
   	
   	public function getAllUsers()
   	{
	   	return $this->call("GET", "user/getAll/", array(), true);
   	}
   	
   	public function deleteUser($vpnuser_id)
   	{
	   	return $this->call("GET", "user/deleteUser/".$vpnuser_id."/", array(), true);
   	}   	
   	
   	public function updateUser($vpnuser_id, $email, $bandwidth, $label, $language)
   	{
	  return $this->call("POST", "user/update/".$vpnuser_id."/", array("email" => $email, "bandwidth" => $bandwidth, "label" => $label, "language" => $language), true); 	
   	}
   	
    public function enableUser($vpnuser_id)
   	{
	   	return $this->call("GET", "user/enable/".$vpnuser_id."/", array(), true);
   	}   
   	
    public function disableUser($vpnuser_id)
   	{
	   	return $this->call("GET", "user/disable/".$vpnuser_id."/", array(), true);
   	}      
   	
   	public function authUser($username, $password)
   	{
	  return $this->call("POST", "user/authenticate/", array("username" => $username, "password" => $password), true); 	
   	}	
   	
   	public function getRegions()
   	{
	   return $this->call("GET", "server/get/", array(), true);	
   	}
   	
   	public function checkUsername($username)
   	{
	   $result = $this->call("POST", "user/checkUsername/", array("username" => $username), true);	
	   return $result["status"];
   	}   	

   	public function changePassword($vpnuser_id, $password)
   	{
	   $result = $this->call("POST", "user/changePassword/".$vpnuser_id, array("password" => $password), true);	
	   return $result["status"];
   	}   
   	   	
}
?>