<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once "lib/DeployVPN.php";

function randomPassword() {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function randomUsername() {
    return "vpn".rand(1000000, 9999999);
}

function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1000)); 
    $pow = min($pow, count($units) - 1); 

    $bytes /= pow(1000, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow]; 
}  
	
function deployvpn_MetaData()
{
    return array(
        'DisplayName' => 'DeployVPN WHMCS Module',
        'APIVersion' => '1.0',
        'RequiresServer' => true, // Set true if module requires a server to work
        'DefaultNonSSLPort' => '80', // Default Non-SSL Connection Port
        'DefaultSSLPort' => '443', // Default SSL Connection Port
        'ServiceSingleSignOnLabel' => 'Login to Panel as User',
        'AdminSingleSignOnLabel' => 'Login to Panel as Admin',
        "language" => "english",
    );
}

function deployvpn_ConfigOptions($params)
{
    return [
        'Bandwidth' => [
            'Type' => 'text',
            'Size' => '25',
            'SimpleMode' => true,
            'Description' => "Set the bandwidth limit in GB. Set to 0 for unlimited.",
            'Default' => "100",
        ],
        'Label' => [
            'Type' => 'text',
            'Size' => '25',
            'SimpleMode' => true,
            'Description' => "Set a label for internal usage.",
            'Default' => "WHMCS Created User",
        ],
		"Term" => [
            "FriendlyName" => "Renewal Term",
            "Type" => "dropdown", # Dropdown Choice of Options
            "Options" => [
                '0' => '1 month',
                '1' => '3 months',
                '2' => '6 months',
                '3' => '1 year',
                '4' => '2 years',
                '5' => '3 years',
            ],
            'SimpleMode' => true,
            "Description" => "Select the renewal term for this product.",
            "Default" => "0",
        ],        
    ];
}

function deployvpn_TestConnection(array $params)
{
	$client = new DeployVPN($params["serverhostname"], $params["serverusername"], $params["serverpassword"]);
	
	$result = $client->checkConnection();

    return array(
        'success' => $result["status"],
        'error' => $result["message"],
    );		
	
}

function deployvpn_CreateAccount(array $params)
{
	if(empty($params['model']->serviceProperties->get('id')))
	{
		$client = new DeployVPN($params["serverhostname"], $params["serverusername"], $params["serverpassword"]);
	
		if(empty($params["username"]))
		{
			if($client->checkUsername($params["clientsdetails"]["email"]))
			{
				$username = $params["clientsdetails"]["email"];
				$params['model']->serviceProperties->save(['username' => $username]);	
			}
			else
			{
				$username = randomUsername();
				$params['model']->serviceProperties->save(['username' => $username]);	
			}
		}
		else
		{
			$username = $params["username"];
		}
		
		$password = randomPassword();
		
		$params['model']->serviceProperties->save(['password' => $password]);
		
		$result = $client->createUser($username, $password, $params["clientsdetails"]["email"], $params["configoption1"], "off", $params["configoption2"], "english", $params["configoption3"]);
	
		if(!empty($result["id"]))
		{
			$params['model']->serviceProperties->save(['id' => $result["id"], 'domain' => $username]);
			return "success";	
		}
		else
		{
			return $result["message"];
		}			
	}
	else
	{
		return "Already assigned a DeployVPN user to this service.";
	}
}

function deployvpn_SuspendAccount(array $params)
{
	$client = new DeployVPN($params["serverhostname"], $params["serverusername"], $params["serverpassword"]);
	$vpnuser_id = $params['model']->serviceProperties->get('id');
	$result = $client->disableUser($vpnuser_id);
	
	if($result["status"])
	{
		return "success";
	}
	else
	{
		return $result["message"];
	}
}

function deployvpn_UnsuspendAccount(array $params)
{
	$client = new DeployVPN($params["serverhostname"], $params["serverusername"], $params["serverpassword"]);
	$vpnuser_id = $params['model']->serviceProperties->get('id');
	$result = $client->enableUser($vpnuser_id);
	
	if($result["status"])
	{
		return "success";
	}
	else
	{
		return $result["message"];
	}
}

function deployvpn_TerminateAccount(array $params)
{
	$client = new DeployVPN($params["serverhostname"], $params["serverusername"], $params["serverpassword"]);
	$vpnuser_id = $params['model']->serviceProperties->get('id');
	$result = $client->deleteUser($vpnuser_id);
	
	if($result["status"])
	{
		$params['model']->serviceProperties->save(['id' => "", 'username' => "", 'password' => ""]);
		return "success";
	}
	else
	{
		return $result["message"];
	}
}

function deployvpn_ChangePassword(array $params)
{
	$client = new DeployVPN($params["serverhostname"], $params["serverusername"], $params["serverpassword"]);
	$vpnuser_id = $params['model']->serviceProperties->get('id');
	$result = $client->changePassword($vpnuser_id, $params["password"]);
	
	if($result["status"])
	{
		return "success";
	}
	else
	{
		return $result["message"];
	}		
}

function deployvpn_AdminServicesTabFields(array $params)
{
	$client = new DeployVPN($params["serverhostname"], $params["serverusername"], $params["serverpassword"]);
    if($client->checkConnection())
    {
	    $vpnuser_id = $params['model']->serviceProperties->get('id');
	    
	    if(!empty($vpnuser_id))
	    {
		    $user = $client->getUser($vpnuser_id);
		  	
		  	if($user["id"] == $vpnuser_id)
		  	{
			  	
			  	if($user["disabled"])
			  		$disabled = '<span class="label label-danger">Disabled</span>';
			  	else
			  		$disabled = '<span class="label label-success">Active</span>';
			  	
			  	
			  	if($user["bandwidthLimit"] == 0)
			  	{
				  	$bandwidthUsage = formatBytes($user["bandwidthUsage"]);
				  	$percentage = 0;
				  	$bandwidthLimit = "&infin;";
			  	}
			  	else
			  	{
				  $bandwidthUsage = formatBytes($user["bandwidthUsage"]);
				  $bandwidthLimit = formatBytes($user["bandwidthLimit"]*1000000000);
				  $percentage = round(($user["bandwidthUsage"]/($user["bandwidthLimit"]*1000000000))*100);	
			  	}

				return array(
					'VPN Access' => $disabled,
		            'Bandwidth Usage' => $bandwidthUsage." / ".$bandwidthLimit." (".$percentage."%)",      
		            'Created Date' =>  $user["created"],
		            'Expire Date' =>  $user["expires"],                    
		        );  				  	
		  	}
		  	else
		  	{
			  return array('API Connection' => $user["message"]); 	
		  	}	    
	    } 
	    else
	    {
		    return array('API Connection' => "No VPN user specified!"); 
	    }
    }
    else
    {
		return array('API Connection' => "Could not establish API Connection."); 		    
    }
}

function deployvpn_ClientArea(array $params)
{
	$client = new DeployVPN($params["serverhostname"], $params["serverusername"], $params["serverpassword"]);
    if($client->checkConnection())
    {
	    $vpnuser_id = $params['model']->serviceProperties->get('id');
	    
	    if(!empty($vpnuser_id))
	    {
		    $user = $client->getUser($vpnuser_id);
		  	
		  	if($user["id"] == $vpnuser_id)
		  	{
			  	$regions = $client->getRegions();
			  	
			  	if($user["bandwidthLimit"] == 0)
			  	{
				  	$bandwidthUsage = formatBytes($user["bandwidthUsage"]);
				  	$percentage = 0;
				  	$bandwidthLimit = "&infin;";
			  	}
			  	else
			  	{
				  $bandwidthUsage = formatBytes($user["bandwidthUsage"]);
				  $bandwidthLimit = formatBytes($user["bandwidthLimit"]*1000000000);
				  $percentage = round(($user["bandwidthUsage"]/($user["bandwidthLimit"]*1000000000))*100);	
			  	}
			  	
			  	return array(
				    'tabOverviewReplacementTemplate' => "overview.tpl",
				    'templateVariables' => array(
				        'user' => $user,
				        'regions' => $regions,
				        'bandwidthUsage' => $bandwidthUsage,
				        'percentage' => $percentage,
				        'bandwidthLimit' => $bandwidthLimit,
				        'lang' => $params['_lang']
				    ),
				);			  	
		  	}
	    } 
    }
}

