<?

	class Homepilot extends IPSModule
	{
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->ConnectParent("{F33A1FA8-85D6-4E2D-BBFF-EA73221221E8}", "Homepilot Splitter"); //Homepilot Splitter
			$this->RegisterPropertyString("Name", "");
			$this->RegisterPropertyInteger("DeviceID", 0);
			$this->RegisterPropertyString("ProductName", "");
			$this->RegisterPropertyString("Version", "");
			$this->RegisterPropertyString("UID", "");
		}
	
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
			
			//$idstring = $this->RegisterVariableString("FlowRequest", "Flow Request", "~String", 2);
			//IPS_SetHidden($idstring, true);
			
			
			
			
			$this->RegisterProfileFloat("Homepilot.Position", "Intensity", "", " %", 0, 100, 1, 0);
			$this->RegisterVariableFloat("HomepilotPosition", "Position", "Homepilot.Position", 1);
			$this->EnableAction("HomepilotPosition");
			$homepilotremoteass =  Array(
					Array(1, "Up",  "", -1),
					Array(2, "Stop",  "", -1),
					Array(3, "Down",  "", -1)
				);
						
			$this->RegisterProfileIntegerAss("Homepilot.Remote", "Jalousie", "", "", 1, 3, 0, 0, $homepilotremoteass);
			$this->RegisterVariableInteger("HomepilotRemote", "Steuerung", "Homepilot.Remote", 2);
			$this->EnableAction("HomepilotRemote");
			$homepilotsetposass =  Array(
					Array(4, "0 %",  "", -1),
					Array(5, "25 %",  "", -1),
					Array(6, "50 %",  "", -1),
					Array(7, "75 %",  "", -1),
					Array(8, "100 %",  "", -1)
				);
						
			$this->RegisterProfileIntegerAss("Homepilot.SetPosition", "Shutter", "", "", 4, 8, 0, 0, $homepilotsetposass);
			$this->RegisterVariableInteger("HomepilotSetPosition", "Position anfahren", "Homepilot.SetPosition", 3);
			$this->EnableAction("HomepilotSetPosition");
			
			$this->ValidateConfiguration();	
		}
		
		private function ValidateConfiguration()
		{
			$Name = $this->ReadPropertyString('Name');
			$DeviceID = $this->ReadPropertyInteger('DeviceID');
					
			//Auswahl Prüfen
			if ($Name !== "" && $DeviceID !== "")
				{
					$this->SetStatus(102);	
				}
		}	
		
		public function SendCommand(integer $command, integer $position)
		{
			$debug = $this->ReadPropertyBoolean('Debug');
			$deviceID = $this->ReadPropertyInteger('DeviceID');
			$payload = array("deviceID" => $deviceID, "command" => $command, "position" => $position);
			$this->SendDebug("Send Data:",json_encode($payload),0);
									
			//an Splitter schicken
			$result = $this->SendDataToParent(json_encode(Array("DataID" => "{D22DE634-29D5-460C-80B6-EEEB48545B2B}", "Buffer" => $payload))); //Homepilot Interface GUI
			return $result;
		}
		
		public function ReceiveData($JSONString)
		{
			$data = json_decode($JSONString);
			$objectid = $data->Buffer->objectid;
			$values = $data->Buffer->values;
			$valuesjson = json_encode($values);
			if (($this->InstanceID) == $objectid)
			{
				//Parse and write values to our variables
				//$this->WriteValues($valuesjson);
			}
		}
		
		public function RequestAction($Ident, $Value)
		{	
			switch($Ident) {
				case "HomepilotPosition":
					SetValue($this->GetIDForIdent("HomepilotPosition"), $Value);
					$command = 9;
					$position = $Value;
					$return = $this->SendCommand($command, $position);
					
					break;
				case "HomepilotRemote":
					SetValue($this->GetIDForIdent("HomepilotRemote"), $Value);
					$command = $Value;
					$position = 0;
					$return = $this->SendCommand($command, $position);
					
					break;
				case "HomepilotSetPosition":
					SetValue($this->GetIDForIdent("HomepilotSetPosition"), $Value);
					$command = $Value;
					$position = 0;
					$return = $this->SendCommand($command, $position);
					
					break;
				default:
					throw new Exception("Invalid ident");
			}
		}
		
		//Commands
		public function Up()
		{
			$command = 1;
			$position = 0;
			$this->SendCommand($command, $position);
		}
		
		public function Stop()
		{
			$command = 2;
			$position = 0;
			$this->SendCommand($command, $position);
		}
		
		public function Down()
		{
			$command = 3;
			$position = 0;
			$this->SendCommand($command, $position);
		}
		
		public function Position0()
		{
			$command = 4;
			$position = 0;
			$this->SendCommand($command, $position);
		}
		
		public function Position25()
		{
			$command = 5;
			$position = 0;
			$this->SendCommand($command, $position);
		}
		
		public function Position50()
		{
			$command = 6;
			$position = 0;
			$this->SendCommand($command, $position);
		}
		
		public function Position75()
		{
			$command = 7;
			$position = 0;
			$this->SendCommand($command, $position);
		}
		
		public function Position100()
		{
			$command = 8;
			$position = 0;
			$this->SendCommand($command, $position);
		}
		
		public function Position(integer $position)
		{
			$command = 9;
			$this->SendCommand($command, $position);
		}
		
		public function On()
		{
			$command = 10;
			$position = 0;
			$this->SendCommand($command, $position);
		}
		
		public function Off()
		{
			$command = 11;
			$position = 0;
			$this->SendCommand($command, $position);
		}
		
		public function Increment()
		{
			$command = 23;
			$position = 0;
			$this->SendCommand($command, $position);
		}
		
		public function Decrement()
		{
			$command = 24;
			$position = 0;
			$this->SendCommand($command, $position);
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
