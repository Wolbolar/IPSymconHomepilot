<?

class HomepilotSplitter extends IPSModule
{

    public function Create()
    {
	//Never delete this line!
        parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
		$this->RequireParent("{D40B39AA-3966-41F1-A7E1-ABFF538DE0CE}", "Homepilot I/O"); //Homepilot I/O
		$this->RegisterPropertyBoolean("Debug", true);

    }

    public function ApplyChanges()
    {
	//Never delete this line!
        parent::ApplyChanges();
        $change = false;
		$debug = $this->ReadPropertyBoolean('Debug');
		if($debug)
		{
			$this->RegisterVariableString("BufferIN", "BufferIN", "", 1);
			$this->RegisterVariableString("CommandOut", "CommandOut", "", 2);
			IPS_SetHidden($this->GetIDForIdent('CommandOut'), true);
			IPS_SetHidden($this->GetIDForIdent('BufferIN'), true);
		}
		
		
		$ParentID = $this->GetParent();
		
			
		// Wenn I/O verbunden ist
		if ($this->HasActiveParent($ParentID))
			{
				//Instanz aktiv
			}

    }

		/**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        *
        *
        */
	
	
	

	################## DUMMYS / WOARKAROUNDS - protected

    protected function GetParent()
    {
        $instance = IPS_GetInstance($this->InstanceID);
        return ($instance['ConnectionID'] > 0) ? $instance['ConnectionID'] : false;
    }

    protected function HasActiveParent($ParentID)
    {
        if ($ParentID > 0)
        {
            $parent = IPS_GetInstance($ParentID);
            if ($parent['InstanceStatus'] == 102)
            {
                $this->SetStatus(102);
                return true;
            }
        }
        $this->SetStatus(203);
        return false;
    }

    private function SetValueBoolean($Ident, $value)
    {
        $id = $this->GetIDForIdent($Ident);
        if (GetValueBoolean($id) <> $value)
        {
            SetValueBoolean($id, $value);
            return true;
        }
        return false;
    }

    private function SetValueInteger($Ident, $value)
    {
        $id = $this->GetIDForIdent($Ident);
        if (GetValueInteger($id) <> $value)
        {
            SetValueInteger($id, $value);
            return true;
        }
        return false;
    }

    private function SetValueString($Ident, $value)
    {
        $id = $this->GetIDForIdent($Ident);
        if (GetValueString($id) <> $value)
        {
            SetValueString($id, $value);
            return true;
        }
        return false;
    }

    protected function SetStatus($InstanceStatus)
    {
        if ($InstanceStatus <> IPS_GetInstance($this->InstanceID)['InstanceStatus'])
            parent::SetStatus($InstanceStatus);
    }

	
	// Data an Child weitergeben
	public function ReceiveData($JSONString)
	{
		$debug = $this->ReadPropertyBoolean('Debug');
		// Empfangene Daten vom Homepilot I/O
		$data = json_decode($JSONString);
		$dataio = json_encode($data->Buffer);
		if($debug)
		{
			SetValueString($this->GetIDForIdent("BufferIN"), $dataio);
			IPS_LogMessage("ReceiveData Homepilot Splitter", utf8_decode($data->Buffer)); //utf8_decode geht nur bei string
		}
		
	 
		// Hier werden die Daten verarbeitet
	 
		// Weiterleitung zu allen Gerät-/Device-Instanzen
		$result = $this->SendDataToChildren(json_encode(Array("DataID" => "{69C786B6-AA98-4093-BDF0-5113BF774A89}", "Buffer" => $data->Buffer))); //Homepilot Splitter Interface GUI
		return $result;
	}
	
			
	################## DATAPOINT RECEIVE FROM CHILD
	

	public function ForwardData($JSONString)
	{
		$debug = $this->ReadPropertyBoolean('Debug');
		// Empfangene Daten von der Device Instanz
		$data = json_decode($JSONString);
		$datasend = $data->Buffer;
		$datasend = json_encode($datasend);
		if($debug)
		{
			SetValueString($this->GetIDForIdent("CommandOut"), $datasend);
			IPS_LogMessage("Homepilot Splitter Forward Data", $datasend);
		}
	 
		// Hier würde man den Buffer im Normalfall verarbeiten
		// z.B. CRC prüfen, in Einzelteile zerlegen
		try
		{
			//
		}
		catch (Exception $ex)
		{
			echo $ex->getMessage();
			echo ' in '.$ex->getFile().' line: '.$ex->getLine().'.';
		}
	 
		// Weiterleiten zur I/O Instanz
		$resultat = $this->SendDataToParent(json_encode(Array("DataID" => "{CCB43094-7AAE-431E-BF7B-662A294CC473}", "Buffer" => $data->Buffer))); //Homepilot TX GUI
	 
		// Weiterverarbeiten und durchreichen
		return $resultat;
	 
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
}

?>