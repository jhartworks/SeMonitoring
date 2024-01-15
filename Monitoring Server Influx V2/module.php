<?

declare(strict_types=1);

include_once __DIR__ . '/../libs/vendor/autoload.php';

use InfluxDB2\Model\WritePrecision;

// Klassendefinition
class MonitoringServerInfluxV2 extends IPSModule {
    // Überschreibt die interne IPS_Create($id) Funktion
    public function Create() {
        // Diese Zeile nicht löschen.
        parent::Create();
        
        $this->RegisterPropertyInteger("ParseNotifyCategoryID","0");
        $this->RegisterPropertyInteger("ParseAlarmCategoryID","0");
        $this->RegisterPropertyInteger("ParseAnalogCategoryID","0");

        $this->RegisterPropertyInteger("ISP","1");
        $this->RegisterPropertyInteger("VisuId","0");

        $this->RegisterPropertyInteger("Projectnumber",123456);
        $this->RegisterPropertyString("Projectname","Musterprojekt");
        $this->RegisterPropertyString("Projectlocation","Urbach");
        $this->RegisterPropertyString("Projecttags","street=Raiffeisenstrasse streetnumber=17");

        $this->RegisterPropertyBoolean("SendNotification","false");

        $this->RegisterPropertyBoolean("LogValues","true");

        $this->RegisterPropertyString("SslHttp","http://");
        $this->RegisterPropertyString("InfluxServer","172.16.9.xxx");
        $this->RegisterPropertyInteger("InfluxPort",8086);

        $this->RegisterPropertyString("InfluxToken","");
        $this->RegisterPropertyString("InfluxOrg","SE-Inno");

        $this->RegisterPropertyInteger("UpdateintervallValues","30");
        $this->RegisterPropertyInteger("UpdateintervallAlarms","10");


        $this->RegisterTimer("UpdateAlarms", 0, 'SEMS_checkAlarms('.$this->InstanceID.');');
        $this->RegisterTimer("UpdateValues", 0, 'SEMS_checkValues('.$this->InstanceID.');');

        $this->RegisterVariableInteger("AlarmVarCount", "Number of Alarmvalues", "", 0);
        $this->RegisterVariableInteger("AlarmActiveCount", "Number of Values in Alarmstate", "", 1);
        $this->RegisterVariableInteger("AnalogVarCount", "Number of Analogvalues", "", 2);
        $this->RegisterVariableInteger("DigitalVarCount", "Number of DigitalValues", "", 3);
        $this->RegisterVariableString("InfluxState", "Influx-DB State", "", 4);
        $this->RegisterVariableString("InfluxMessage", "Influx-DB Message", "", 5);

    }




    // Überschreibt die intere IPS_ApplyChanges($id) Funktion
    public function ApplyChanges() {

        $this->SetTimerInterval("UpdateAlarms", $this->ReadPropertyInteger("UpdateintervallAlarms") * 1000);
        $this->SetTimerInterval("UpdateValues", $this->ReadPropertyInteger("UpdateintervallValues") * 1000);

        // Diese Zeile nicht löschen
        parent::ApplyChanges();
    }
    /**
    * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
    * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
    *
    *
    */

    public $client = "";
    public $writeApi = "";
    public $state = "";
    public function connectInflux(){

            $ssl = $this->ReadPropertyString("SslHttp");
            $server = $this->ReadPropertyString("InfluxServer");
            $port = $this->ReadPropertyInteger("InfluxPort");
            $token = $this->ReadPropertyString("InfluxToken");
            $org = $this->ReadPropertyString("InfluxOrg");
            $projectnumber = $this->ReadPropertyInteger("Projectnumber");
            $ispnumber = $this->ReadPropertyInteger("ISP");


        $this->client = new InfluxDB2\Client([
            "url" => $ssl.$server.":".$port,
            "token" => $token,
            "bucket" => "P".$projectnumber."_ISP".$ispnumber,
            "org" => $org,
            "precision" => InfluxDB2\Model\WritePrecision::S
        ]);
        $this->writeApi = $this->client->createWriteApi();

        $this->state = $this->client->health();

        SetValueString($this->GetIDForIdent("InfluxState"),$this->state["status"]);
        SetValueString($this->GetIDForIdent("InfluxMessage"),$this->state["message"]);

    }


    public function checkInfluxState(){

        $this->connectInflux();
        print_r($this->client->health());

    }


    public function checkAlarms() {
            $catAlarmId  = $this->ReadPropertyInteger("ParseAlarmCategoryID");
            $visuId = $this->ReadPropertyInteger("VisuId");

            $projectnumber = $this->ReadPropertyInteger("Projectnumber");
            $projectname = $this->ReadPropertyString("Projectname");
            $ispnumber = $this->ReadPropertyInteger("ISP");

            $sendit = $this->ReadPropertyBoolean("SendNotification");
            $inalarmcount = 0;
            $alarmvalues = 0;
            $catChilds = IPS_GetChildrenIDs($catAlarmId);

            if ($catAlarmId > 0){
                foreach ($catChilds as $catChild) {
                

                    $childids = IPS_GetChildrenIDs($catChild);
                    $parname = IPS_GetName($catChild);
    
                        foreach ($childids as $childid){
    
                            $varInfo = IPS_GetVariable($childid);
                            $changedtime = $varInfo["VariableChanged"];
                            $varname = IPS_GetName($childid);
                            $time = time();
                            $payload = GetValueBoolean($childid);
                            $alarmvalues++;
                            if ($sendit == true){
                                if ($this->notify($childid, $visuId, $catAlarmId, $projectnumber, $projectname, $ispnumber) == true){
                                    $inalarmcount++;
                                }
                            }
    
    
                        }
                
                }   
            }
            SetValueInteger($this->GetIDForIdent("AlarmVarCount"),$alarmvalues);
            SetValueInteger($this->GetIDForIdent("AlarmActiveCount"),$inalarmcount);
            $rootcat = IPS_GetParent($catAlarmId);
            if ($inalarmcount > 0 && $rootcat >0){
                    IPS_SetIcon($rootcat,"Alert");
            }else{
                    IPS_SetIcon($rootcat,"");
            }
    }


    public function checkValues() {

        $catNotifyId  = $this->ReadPropertyInteger("ParseNotifyCategoryID");
        $catAnalogId  = $this->ReadPropertyInteger("ParseAnalogCategoryID");

        $projectnumber = $this->ReadPropertyInteger("Projectnumber");
        $projectname = $this->ReadPropertyString("Projectname");
        $projectlocation = $this->ReadPropertyString("Projectlocation");
        $projecttags = $this->ReadPropertyString("Projecttags");
        $updatetime = $this->ReadPropertyInteger("UpdateintervallValues");
        $ispnumber = $this->ReadPropertyInteger("ISP");
        $org = $this->ReadPropertyString("InfluxOrg");

        $logit = $this->ReadPropertyBoolean("LogValues");

        $catChilds = IPS_GetChildrenIDs($catAnalogId);
        $numberofvalues = 0;

        $this->connectInflux();

        if ($catAnalogId > 0){

            foreach ($catChilds as $catChild) { //for each object in category
            

                $objchildids = IPS_GetChildrenIDs($catChild); //get variables of objects
                $parname = IPS_GetName($catChild);
    
                    foreach ($objchildids as $objchildid){
    
                        $varInfo = IPS_GetVariable($objchildid);
                        $changedtime = $varInfo["VariableChanged"];
                        $varname = IPS_GetName($objchildid);
                        $time = time();
                        $payload = GetValue($objchildid);
                        if(is_float($payload)) {
                            $payload = round($payload, 2);
                        }

                        
                        $numberofvalues++;
                        if($changedtime > $time - $updatetime){

                            $parname = str_replace(" ","", $parname);
                            $varname = str_replace(" ","", $varname);
                            
                            $dataArray = [
                                'name' => $parname."_".$varname,
                                'tags' => ["projectname=".$projectname." location=".$projectlocation." ".$projecttags],
                                'fields' => ["value" => $payload],
                                'time' => $time
                            ];

                            if($this->state["status"] == "pass"){
                                $this->writeApi->write($dataArray, WritePrecision::S, "P".$projectnumber."_ISP".$ispnumber, $org);
                            }
    
                        }
    
    
                    }
            
            } 
        }
        SetValueInteger($this->GetIDForIdent("AnalogVarCount"),$numberofvalues);
  


}


    private function notify($trigid, $webfrontid, $targetid, $projectnumber, $projectname, $ispnr){
        

                $art = $projectnumber." | ".$projectname. " | ISP ".$ispnr;
                $ico = "Flame";

            $smname = IPS_GetName(IPS_GetParent($trigid))."_".IPS_GetName($trigid);

            if (GetValueBoolean($trigid) == true && ($this->GetBuffer($smname) == "true"))  {
                
                $this->SetBuffer($smname, "false");   

                VISU_PostNotification($webfrontid, $art, $smname, $ico, $targetid);
                WFC_PushNotification($webfrontid, $art, $smname, "", $targetid);

                
            }
            elseif (GetValueBoolean($trigid) == false) {
                $this->SetBuffer($smname, "true");
            }

            if (GetValueBoolean($trigid) == true){
                return true; 
            }else{
                return false;
            }
    }
}
?>