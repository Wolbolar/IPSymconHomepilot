<?

class HomepilotIO extends IPSModule
{

    public function Create()
    {
	//Never delete this line!
        parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
		$this->RegisterPropertyString("Host", "");
		$this->RegisterPropertyInteger("Port", 5222);
        $this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("ImportCategoryID", 0);
		$this->RegisterPropertyString("username", "ipsymcon");
		$this->RegisterPropertyString("password", "user@h0me");
		$this->RegisterPropertyBoolean("Debug", true);
		//local test
		$this->RegisterPropertyBoolean("Test", false);
    }

    public function ApplyChanges()
    {
	//Never delete this line!
        parent::ApplyChanges();
        $change = false;
		
		$this->RegisterVariableString("HomepilotConfig", "Homepilot Config", "", 1);
		IPS_SetHidden($this->GetIDForIdent('HomepilotConfig'), true);
		$this->ValidateConfiguration();
	}	

	/**
    * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
    * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
    *
    *
    */
		

	
	private function ValidateConfiguration()
	{
		$debug = $this->ReadPropertyBoolean('Debug');
		$change = false;
		
		if($debug)
		{
			$this->RegisterVariableString("CommandOut", "CommandOut", "", 2);
			$this->RegisterVariableString("IOIN", "IOIN", "", 3);
			IPS_SetHidden($this->GetIDForIdent('CommandOut'), true);
			IPS_SetHidden($this->GetIDForIdent('IOIN'), true);
		}
		
		$ip = $this->ReadPropertyString('Host');
		$username = $this->ReadPropertyString('username');
		$password = $this->ReadPropertyString('password');
		
		//IP prüfen
		if (!filter_var($ip, FILTER_VALIDATE_IP) === false)
			{
				//ok
			}
		else
			{
			$this->SetStatus(203); //IP Adresse ist ungültig 
			}
		$change = false;	
		//User und Passwort prüfen
		if ($username == "" || $password == "")
			{
				$this->SetStatus(205); //Felder dürfen nicht leer sein
			}
		elseif ($username !== "" && $password !== "" && (!filter_var($ip, FILTER_VALIDATE_IP) === false))
			{
				$config = $this->getConfig();
				SetValue($this->GetIDForIdent("HomepilotConfig"), $config);
				$change = true;	
			}
		
		//Import Kategorie für HarmonyHub Geräte
		$ImportCategoryID = $this->ReadPropertyInteger('ImportCategoryID');
		if ( $ImportCategoryID === 0)
			{
				// Status Error Kategorie zum Import auswählen
				$this->SetStatus(206);
			}
		elseif ( $ImportCategoryID != 0)	
			{
				// Status Aktiv
				$this->SetStatus(102);
			}
	}
	
	protected function getURL($type)
	{
		$ip = $this->ReadPropertyString('Host');
		if($type == "GetConfig")
		{
			$url = "http://".$ip."/deviceajax.do?devices=1";
		}
		if($type == "SendCommand")
		{
			$url = "http://".$ip."/deviceajax.do";
		}
		return $url;
	}

	public function getConfig()
	{
		$test = $this->ReadPropertyBoolean('Test');
		if(!$test)
		{
			$url = $this->getURL("GetConfig");
			$responsejson = file_get_contents($url);
			SetValue($this->GetIDForIdent("HomepilotConfig"), $responsejson);
		}	
		else
		{
		$responsejson = GetValue($this->GetIDForIdent("HomepilotConfig"));	
		}
	
	return $responsejson;
	}
	
	//Status abholen
	public function GetState()
	{
		$HomepilotConfig = $this->getConfig();
		$CategoryID = $this->ReadPropertyInteger('ImportCategoryID');
		$response = json_decode($HomepilotConfig);
		$devices = $response->devices;
		foreach ($devices as $key => $device)
		{
			$deviceID = $device->did; //DeviceID des Geräts
			$position = $device->position; //Position des Geräts
			$deviceobjid = IPS_GetObjectIDByIdent($deviceID, $CategoryID);
			$positionid = IPS_GetObjectIDByIdent("HomepilotPosition", $deviceobjid);
			SetValue($positionid, $position);
		}	
	}
	
	//Profile zuweisen und Geräte anlegen
	public function SetupDevices()
	{
		//Konfig prüfen
		$HomepilotConfig = GetValue($this->GetIDForIdent("HomepilotConfig"));
		if($HomepilotConfig == "")
		{
			$timestamp = time();
			$this->getConfig();
			$i = 0;
			do
			{
			IPS_Sleep(10);
			$updatetimestamp = IPS_GetVariable($this->GetIDForIdent("HomepilotConfig"))["VariableUpdated"];

			//echo $i."\n";
			$i++;
			}
			while($updatetimestamp <= $timestamp);
			$HomepilotConfig = GetValue($this->GetIDForIdent("HomepilotConfig"));
		}
		
		//Homepilot Devices anlegen
		$this->SetupHomepilotInstance($HomepilotConfig);
		return $HomepilotConfig;
	}
	
	//Installation Homepilot Instanzen
	protected function SetupHomepilotInstance($HomepilotConfig)
	{
  		$debug = $this->ReadPropertyBoolean('Debug');
		$CategoryID = $this->ReadPropertyInteger('ImportCategoryID');
		
		$response = json_decode($HomepilotConfig);
		$command = $response->response;
		$status = $response->status;
		$devices = $response->devices;
		$InsIDList = array();
		foreach ($devices as $key => $device)
		{
			$deviceID = $device->did; //DeviceID des Geräts
			$name = $device->name; //Bezeichnung Homepilot Device
			$description = $device->description;
			$initialized = $device->initialized;
			$position = $device->position; //Position des Geräts
			$productName = $device->productName;
			$version = $device->version;
			$uid = $device->uid;
			$serial = $device->serial;
			$statusPosition = $device->statusesMap->Position;
			$statusManu = $device->statusesMap->Manuellbetrieb;
			$status_changed = $device->status_changed;
			$deviceGroup = $device->deviceGroup;
			$iconname = $device->iconSet->name;
			$icondescription = $device->iconSet->description;
			$iconstrMin = $device->iconSet->strMin;
			$iconstrMax = $device->iconSet->strMax;
			$iconvalMax = $device->iconSet->valMax;
			$iconvalMin = $device->iconSet->valMin;
			$iconnumTiles = $device->iconSet->sprite->numTiles;
			$iconimageUri = $device->iconSet->sprite->imageUri;
			$iconset = $device->iconSet->k;	
			
			$InsID = $this->CreateDeviceInstance($name, $CategoryID, $deviceID, $position, $productName, $version, $uid);
			$InsIDList[] = $InsID;
		}		
	}

	//Create Homepilot Device Instance 
	protected function CreateDeviceInstance(string $name, integer $CategoryID, integer $deviceID, integer $position, string $productName, string $version, string $uid)
	{
		
		//Prüfen ob Instanz schon existiert
		$InsID = @IPS_GetObjectIDByIdent($deviceID, $CategoryID);
		if ($InsID === false)
			{
				//Neue Instanz anlegen
				$InsID = IPS_CreateInstance("{19E9190A-F772-4589-8655-5FB219F6C418}");
				$InstName = (string)$name;
				IPS_SetName($InsID, $InstName); // Instanz benennen
				IPS_SetInfo($InsID, $deviceID);
				IPS_SetIdent($InsID, $deviceID);
				IPS_SetParent($InsID, $CategoryID); // Instanz einsortieren unter dem Objekt mit der ID "$CategoryID"
				IPS_SetProperty($InsID, "Name", $InstName); //Name setzten.
				IPS_SetProperty($InsID, "DeviceID", $deviceID); //DeviceID setzten.
				IPS_SetProperty($InsID, "ProductName", $productName); //Produktname setzten.
				IPS_SetProperty($InsID, "Version", $version); //Version setzten.
				IPS_SetProperty($InsID, "UID", $uid); //UID setzten.
				IPS_ApplyChanges($InsID); //Neue Konfiguration übernehmen
				IPS_Sleep(2000);
				
				IPS_LogMessage( "Homepilot" , "Homepilot Instanz Name: ".$InstName." erstellt" );
				
			}
		
		$positionID = @IPS_GetObjectIDByIdent("HomepilotPosition", $InsID);
		/*
		if($positionID == false)
		{
			$timestamp = time();
			$positionID = @IPS_GetObjectIDByIdent("HomepilotPosition", $InsID);
			$i = 0;
			do
			{
			IPS_Sleep(10);
			$updatetimestamp = IPS_GetVariable($this->GetIDForIdent("HomepilotPosition"))["VariableUpdated"];

			//echo $i."\n";
			$i++;
			}
			while($updatetimestamp <= $timestamp);
		}
		*/
		SetValue($positionID, $position);
		return $InsID;
	}
	
	
################## Datapoints
 
	
		
			
	################## DATAPOINT RECEIVE FROM CHILD
	

	public function ForwardData($JSONString)
	{
	 
		// Empfangene Daten von der Splitter Instanz
		$data = json_decode($JSONString);
		
	 
		// Hier würde man den Buffer im Normalfall verarbeiten
		// z.B. CRC prüfen, in Einzelteile zerlegen
		try
		{
			// Absenden an Homepilot
		
			//IPS_LogMessage("Forward Data to Flow", utf8_decode($data->Buffer));
			
			//aufarbeiten
			$payload = $data->Buffer;
			$result = $this->SendCommand ($payload);
		}
		catch (Exception $ex)
		{
			echo $ex->getMessage();
			echo ' in '.$ex->getFile().' line: '.$ex->getLine().'.';
		}
	 
		return $result;
	}
		
	
	protected function SendJSON ($data)
	{
		// Weiterleitung zu allen Gerät-/Device-Instanzen
		$this->SendDataToChildren(json_encode(Array("DataID" => "{659EA94E-E5FB-40E8-A274-4675CA398B27}", "Buffer" => $data))); //Homepilot I/O RX GUI
	}
	
	protected function SendCommandHomepilot($deviceID, $command, $position)
	{
		$debug = $this->ReadPropertyBoolean('Debug');
		$ip = $this->ReadPropertyString('Host');
		$url = "http://" . $ip . "/deviceajax.do";
		$commandhomepilot = $this->GetPostFields($deviceID, $command, $position);
		if($debug)
		{
			SetValue($this->GetIDForIdent("CommandOut"), $commandhomepilot);
			IPS_LogMessage("Homepilot:", utf8_decode($commandhomepilot)." gesendet.");
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout after 5 seconds
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $commandhomepilot);
		$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
		$result=curl_exec ($ch);
		curl_close ($ch);
		return $result;
	}
	
	protected function GetPostFields($deviceID, $command, $position)
	{
		if($command = 9)
		{
			$commandhomepilot = "cid=".$command."&did=".$deviceID."&goto=".$position."&command=1";
		}
		else
		{
			$commandhomepilot = "cid=".$command."&did=".$deviceID."&command=1";
		}
		
		return $commandhomepilot;
	}
	
	protected function SendCommand ($payload)
	{
				
		//Semaphore setzen
        if ($this->lock("TriggerSend"))
        {
        // Daten senden
	        try
	        {
				$deviceID = $payload->deviceID;
				$command = $payload->command;
				$position = $payload->position;
				$result = $this->SendCommandHomepilot($deviceID, $command, $position);
	        }
	        catch (Exception $exc)
	        {
	            // Senden fehlgeschlagen
	            $this->unlock("TriggerSend");
	            throw new Exception($exc);
	        }
        $this->unlock("TriggerSend");
        }
        else
        {
			echo "Can not send to parent \n";
			$result = false;
			$this->unlock("TriggerSend");
			//throw new Exception("Can not send to parent",E_USER_NOTICE);
		  }
		
		return $result;
	
	}
	
	
	################## SEMAPHOREN Helper  - private

    private function lock($ident)
    {
        for ($i = 0; $i < 3000; $i++)
        {
            if (IPS_SemaphoreEnter("Homepilot_" . (string) $this->InstanceID . (string) $ident, 1))
            {
                return true;
            }
            else
            {
                IPS_Sleep(mt_rand(1, 5));
            }
        }
        return false;
    }

    private function unlock($ident)
    {
          IPS_SemaphoreLeave("Homepilot_" . (string) $this->InstanceID . (string) $ident);
    }
	
	protected function GetIPSVersion ()
		{
			$ipsversion = IPS_GetKernelVersion ( );
			$ipsversion = explode( ".", $ipsversion);
			$ipsmajor = intval($ipsversion[0]);
			$ipsminor = intval($ipsversion[1]);
			if($ipsminor < 10) // 4.0
			{
				$ipsversion = 0;
			}
			elseif ($ipsminor >= 10 && $ipsminor < 20) // 4.1
			{
				$ipsversion = 1;
			}
			else   // 4.2
			{
				$ipsversion = 2;
			}
			return $ipsversion;
		}
		
	//Profile
	protected function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits)
	{
        
        if(!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 1);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if($profile['ProfileType'] != 1)
            throw new Exception("Variable profile type does not match for profile ".$Name);
        }
        
        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
		IPS_SetVariableProfileDigits($Name, $Digits); //  Nachkommastellen
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize); // string $ProfilName, float $Minimalwert, float $Maximalwert, float $Schrittweite
        
    }
	
	protected function RegisterProfileIntegerAss($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits, $Associations)
	{
        if ( sizeof($Associations) === 0 ){
            $MinValue = 0;
            $MaxValue = 0;
        } 
		/*
		else {
            //undefiened offset
			$MinValue = $Associations[0][0];
            $MaxValue = $Associations[sizeof($Associations)-1][0];
        }
        */
        $this->RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits);
        
		//boolean IPS_SetVariableProfileAssociation ( string $ProfilName, float $Wert, string $Name, string $Icon, integer $Farbe )
        foreach($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
        
    }

	protected function RegisterProfileFloat($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits)
	{
        
        if(!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 2);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if($profile['ProfileType'] != 2)
            throw new Exception("Variable profile type does not match for profile ".$Name);
        }
        
        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
		IPS_SetVariableProfileDigits($Name, $Digits); //  Nachkommastellen
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
        
    }

	protected function RegisterProfileFloatAss($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits, $Associations)
	{
        if ( sizeof($Associations) === 0 ){
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[sizeof($Associations)-1][0];
        }
        
        $this->RegisterProfileFloat($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits);
        
		//boolean IPS_SetVariableProfileAssociation ( string $ProfilName, float $Wert, string $Name, string $Icon, integer $Farbe )
        foreach($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
        
    }
}

?>