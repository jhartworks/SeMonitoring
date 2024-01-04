<?
// Klassendefinition
class MonitoringClient extends IPSModule {
    // Überschreibt die interne IPS_Create($id) Funktion
    public function Create() {
        // Diese Zeile nicht löschen.
        parent::Create();
        
        $this->RegisterPropertyInteger("ParseCategoryID","0");
        $this->RegisterPropertyInteger("MqttCLientID","0");
        $this->RegisterPropertyString("Projectnumber","");
        $this->RegisterPropertyString("Projectname","");
        $this->RegisterPropertyInteger("ISP","1");
        $this->RegisterPropertyInteger("Updatetime","20");
        $this->RegisterTimer("Update", 0, 'SEMC_SendTopic('.$this->InstanceID.');');

    }

    // Überschreibt die intere IPS_ApplyChanges($id) Funktion
    public function ApplyChanges() {
        // Diese Zeile nicht löschen
        parent::ApplyChanges();

        $this->SetTimerInterval("Update", $this->ReadPropertyInteger("Updatetime") * 1000);

    }
    /**
    * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
    * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
    *
    * DWM_SendMessage($id);
    *
    */
    /* Publish a given payload over a MQTT server under
    a given topic without having to manually create a
    MQTT server device */
    public function MqttPublish($server_id, $topic, $payload, $retain) {
        // ensure server instance exists
        if(!IPS_InstanceExists($server_id)) {
            return false;
        }

        // convert array structure to json string
        if(is_array($payload)) $payload = json_encode($payload);

        // determine data type
        if(is_string($payload)) {
            $ips_var_type = 3;
        } else if(is_float($payload)) {
            $ips_var_type = 2;
        } else if(is_int($payload)) {
            $ips_var_type = 1;
        } else if(is_bool($payload)) {
            $ips_var_type = 0;
        } else { // unsupported
            return false;
        }

        $module_id = "{01C00ADD-D04E-452E-B66A-D253278743FE}" /* Module ID of MQTT Server Device */;
        $ident = "TempMQTTDevice";

        // enter semaphore to ensure the temporary device gets used by one thread at a time
        if(IPS_SemaphoreEnter($ident, 100)) {
            // get temporary MQTT Server Device or create if needed
            $id = @IPS_GetObjectIDByIdent($ident, $_IPS['SELF']);
            if($id === false) {
                $id = @IPS_CreateInstance($module_id);
                if($id === false) {
                    return false;
                }
                IPS_SetParent($id, $_IPS['SELF']);
                IPS_SetIdent($id, $ident);
            }

            // ensure the specified server instance is actually compatible
            if(!IPS_IsInstanceCompatible($id, $server_id)) {
                return false;
            }

            // ensure that the temporary device is actually connected to the correct server instance
            $inst_config = IPS_GetInstance($id);
            if($inst_config["ConnectionID"] != $server_id) {
                IPS_DisconnectInstance($id);
                if(!@IPS_ConnectInstance($id, $server_id)) {
                    return false;
                }
            }

            // name object to help with debugging
            IPS_SetName($id, "Temporary MQTT Server Device for topic " . $topic);

            // configure temporary device
            $config_arr = array(
                "Retain" => $retain,
                "Topic" => $topic,
                "Type" => $ips_var_type
            );
            $config_str = json_encode($config_arr);
            IPS_SetConfiguration($id, $config_str);
            IPS_ApplyChanges($id);

            // get Value variable and use it to publish the payload
            $var_id = @IPS_GetObjectIDByIdent("Value", $id);
            RequestAction($var_id, $payload);

            IPS_SemaphoreLeave($ident);
        } else { // semaphore timeout
            return false;
        }

        return true;
    } // MQTT_Publish

    public function SendTopic() {
   
            // Discord webhook URL
            $catId = $this->ReadPropertyInteger("ParseCategoryID");
            $mqttId = $this->ReadPropertyInteger("MqttCLientID");
            $projectnumber = $this->ReadPropertyString("Projectnumber");
            $projectname = $this->ReadPropertyString("Projectname");
            $ispnumber = $this->ReadPropertyInteger("ISP");
            $updatetime = $this->ReadPropertyInteger("Updatetime");

            $catChilds = IPS_GetChildrenIDs($catId);

            foreach ($catChilds as $catChild) {

                $childids = IPS_GetChildrenIDs($catChild);

                $parname = IPS_GetName($catChild);

                    foreach ($childids as $childid){

                        $varInfo = IPS_GetVariable($childid);
                        $changedtime = $varInfo["VariableChanged"];
                        $varname = IPS_GetName($childid);
                        $topic = $projectnumber. "/ISP" .$ispnumber. "/". $parname."_".$varname;
                        $time = time();
                        $payload = getvalue($childid);
                        if($changedtime > time() - $updatetime){
                            SEMC_MqttPublish($this->InstanceID,$mqttId, $topic, $payload, false);
                        }


                    }
            
            }

    }



}
?>