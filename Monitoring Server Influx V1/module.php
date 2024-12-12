<?

// Klassendefinition
class MonitoringServer extends IPSModule {
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
        $this->RegisterPropertyString("InfluxDatabase","monitoring");

        $this->RegisterPropertyBoolean("SendNotification","false");

        $this->RegisterPropertyBoolean("LogValues","true");

        $this->RegisterPropertyString("SslHttp","http://");
        $this->RegisterPropertyString("InfluxServer","172.16.9.106");
        $this->RegisterPropertyInteger("InfluxPort",8086);


        $this->RegisterPropertyInteger("UpdateintervallValues","30");
        $this->RegisterPropertyInteger("UpdateintervallAlarms","10");
        $this->RegisterPropertyInteger("UpdateintervallForceValues","30");


        $this->RegisterTimer("UpdateAlarms", 0, 'SEMS_checkAlarms('.$this->InstanceID.');');
        $this->RegisterTimer("UpdateValues", 0, 'SEMS_checkValues('.$this->InstanceID.',false);');
        $this->RegisterTimer("ForceUpdateValues", 0, 'SEMS_checkValues('.$this->InstanceID.',true);');

        $this->RegisterVariableInteger("AlarmVarCount", "Number of Alarmvalues", "", 0);
        $this->RegisterVariableInteger("AlarmActiveCount", "Number of Values in Alarmstate", "", 1);
        $this->RegisterVariableInteger("AnalogVarCount", "Number of Analogvalues", "", 2);
        $this->RegisterVariableInteger("DigitalVarCount", "Number of DigitalValues", "", 3);

        $this->RegisterVariableString("Alarmtable", "Alarmhistorie", "~HTMLBox", 30);
        $this->RegisterAttributeString("AtAlarmtable", "");



    }




    // Überschreibt die intere IPS_ApplyChanges($id) Funktion
    public function ApplyChanges() {

        $this->SetTimerInterval("UpdateAlarms", $this->ReadPropertyInteger("UpdateintervallAlarms") * 1000);
        $this->SetTimerInterval("UpdateValues", $this->ReadPropertyInteger("UpdateintervallValues") * 1000);
        $this->SetTimerInterval("ForceUpdateValues", $this->ReadPropertyInteger("UpdateintervallForceValues") * 1000 * 60);

        $this->clearNames();

        // Diese Zeile nicht löschen
        parent::ApplyChanges();
    }
    /**
    * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
    * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
    *
    *
    */

    public function checkInfluxState(){

        $domain = $this->ReadPropertyString("InfluxServer");
        $port = $this->ReadPropertyInteger("InfluxPort");

        $starttime = microtime(true);
        $file      = fsockopen ($domain, $port, $errno, $errstr, 10);
        $stoptime  = microtime(true);
        $status    = 0;
    
        if (!$file) $status = -1;  // Site is down
        else {
            fclose($file);
            $status = ($stoptime - $starttime) * 1000;
            $status = floor($status);
        }
        if ($status  > -1){
           // print_r("Everything fine :)");
        }
        return $status;     
    }

    public function Write2Influx($value, $ssl, $host, $port, $db, $system, $category, $valuename)
    {

        $this->clearNames();

        $out = $ssl.$host.':'.$port.'/write?db='.$db.'';
        
        IPS_LogMessage ("Write 2 Influx", "Output: ".$out);


        $ch = curl_init($out);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_POST,           1 );

        IPS_LogMessage ("Write 2 Influx", "Posted Fields: ".$system.','.$category.'='.$valuename.' value=');
        IPS_LogMessage ("Write 2 Influx", "Value: ".$value);


        if ($value == "true"){
            curl_setopt($ch, CURLOPT_POSTFIELDS,     $system.','.$category.'='.$valuename.' value=1');
          //  echo 'es war ein bool der TRUE war ';
            }
        elseif($value == "false"){
            curl_setopt($ch, CURLOPT_POSTFIELDS,     $system.','.$category.'='.$valuename.' value=0');
            
        //echo 'es war ein bool ';
        }
        else{
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $system.','.$category.'='.$valuename.' value=' .$value);
        //echo 'es war ein float/int ';
        }

        $result=curl_exec ($ch);
        $error=curl_error($ch) ;
        
        
    }

    public function checkAlarms() {

         $this->clearNames();

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
                            $payload = GetValue($childid);
                            $alarmvalues++;
                            if ($sendit == true){
                                if ($this->notify($childid, $visuId, $catAlarmId, $projectnumber, $projectname, $ispnumber) == true){
                                    $inalarmcount ++;
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


    public function checkValues($force) {

        $this->clearNames();

        $catNotifyId  = $this->ReadPropertyInteger("ParseNotifyCategoryID");
        $catAnalogId  = $this->ReadPropertyInteger("ParseAnalogCategoryID");

        $projectnumber = $this->ReadPropertyInteger("Projectnumber");
        $projectname = $this->ReadPropertyString("Projectname");
        $updatetime = $this->ReadPropertyInteger("UpdateintervallValues");
        $ispnumber = $this->ReadPropertyInteger("ISP");

        $logit = $this->ReadPropertyBoolean("LogValues");


        $numberofvalues = 0;

        $ssl = $this->ReadPropertyString("SslHttp");
        $server = $this->ReadPropertyString("InfluxServer");
        $port = $this->ReadPropertyInteger("InfluxPort");
        $db = $this->ReadPropertyString("InfluxDatabase");



        if ($catAnalogId > 0){

            $catChilds = IPS_GetChildrenIDs($catAnalogId);

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
                        if(($changedtime > $time - $updatetime) | $force){

                            $parname = str_replace(" ","", $parname);
                            $varname = str_replace(" ","", $varname);

                            $parname = str_replace("(","", $parname);
                            $varname = str_replace("(","", $varname);

                            $parname = str_replace(")","", $parname);
                            $varname = str_replace(")","", $varname);

                            $parname = str_replace("/","_", $parname);
                            $varname = str_replace("/","_", $varname);

                            $parname = str_replace("-","_", $parname);
                            $varname = str_replace("-","_", $varname);

                            $parname = str_replace("ä","ae", $parname);
                            $varname = str_replace("ä","ae", $varname);

                            $parname = str_replace(".","", $parname);
                            $varname = str_replace(".","", $varname);

                            $parname = str_replace("ö","oe", $parname);
                            $varname = str_replace("ö","oe", $varname);

                            $parname = str_replace("ü","ue", $parname);
                            $varname = str_replace("ü","ue", $varname);

                            $parname = str_replace(",","", $parname);
                            $varname = str_replace(",","", $varname);


                            $system = "P".$projectnumber."_ISP".$ispnumber;
                            $category =  $system."_Analog";
                            $valuename = $parname."".$varname;

                            //IPS_LogMessage ("Analog Var-Logger", "Ready: ".$system."/".$category."/".$valuename." with Value: ".$payload);
                            if ($logit == true){
                                if($this->checkInfluxState() > -1){ 
                                    $this->Write2Influx($payload, $ssl, $server, $port, $db, $system, $category, $valuename);
                                    //IPS_LogMessage ("Analog Var-Logger", "LOGGED: ".$system."/".$category."/".$valuename." with Value: ".$payload);
                                }
                            }

                        }
                       
    
    
                    }
            
            } 
        }

        SetValueInteger($this->GetIDForIdent("AnalogVarCount"),$numberofvalues);
        $numberofvalues = 0;


        if ($catNotifyId > 0){

            $catChilds = IPS_GetChildrenIDs($catNotifyId);

            foreach ($catChilds as $catChild) { //for each object in category
            

                $objchildids = IPS_GetChildrenIDs($catChild); //get variables of objects
                $parname = IPS_GetName($catChild);
    
                    foreach ($objchildids as $objchildid){
    
                        $varInfo = IPS_GetVariable($objchildid);
                        $changedtime = $varInfo["VariableChanged"];
                        $varname = IPS_GetName($objchildid);
                        $time = time();
                        $payload = GetValue($objchildid);
                        
                        $numberofvalues++;
                        if($changedtime > $time - $updatetime){

                            $parname = str_replace(" ","", $parname);
                            $varname = str_replace(" ","", $varname);
                            
                        $system = "P".$projectnumber."_ISP".$ispnumber;
                        $category = $system."_Digital";
                        $valuename = $parname."_".$varname;

                        if ($logit == true){
                            if($this->checkInfluxState() > -1){ 
                                $this->Write2Influx($payload, $ssl, $server, $port, $db, $system, $category, $valuename);
                            }
                        }

                       }
    
    
                    }
            
            } 
        }
        SetValueInteger($this->GetIDForIdent("DigitalVarCount"),$numberofvalues);

}


    private function notify($trigid, $webfrontid, $targetid, $projectnumber, $projectname, $ispnr){
        
        $tdOld = $this->ReadAttributeString("AtAlarmtable");
        $td = $tdOld;

        $time = date("Y-m-d H:i:s");

                $art = $projectnumber." | ".$projectname. " | ISP ".$ispnr;
                $ico = "Flame";

            $smname = IPS_GetName(IPS_GetParent($trigid))."_".IPS_GetName($trigid);

            if (GetValue($trigid) == true && ($this->GetBuffer($smname) == "true"))  {
                
                $this->SetBuffer($smname, "false");   

                VISU_PostNotification($webfrontid, $art, $smname, $ico, $targetid);

                $td = '<tr><td>'.$smname.'</td><td>'.$time.'</td></tr>'.$tdOld;
                $this->WriteAttributeString("AtAlarmtable", $td);
                //IPS_LogMessage ("Notify", "Alarmhistory updated");

                
            }
            elseif (GetValue($trigid) == false) {
                $this->SetBuffer($smname, "true");

            }
            $style = '<style> 

            th {
            background-color: lightblue;
            color: white;
              }
          
            td {
            border-top: 1px solid grey;
            text-align: center; 
              }
          
            tr:hover {background-color: grey;}
          
          
            </style>
          
          
          
          ';
            $table = $style.'<table width=100%><tr><th>Name</th><th>Zeit / Datum</th></tr>'.$td.'</table>';
            SetValueString($this->GetIDForIdent("Alarmtable"), $table);
            IPS_LogMessage ("Notify", "Alarmhistory writen");

            if (GetValue($trigid) == true){
                return true; 
            }else{
                return false;
            }



    }

    private function getTextAfterLastSlash($inputString) {
        $lastSlashPosition = strrpos($inputString, '/');
        if ($lastSlashPosition !== false) {
            return substr($inputString, $lastSlashPosition + 1);
        } else {
            // Wenn kein Schrägstrich gefunden wurde, gib den gesamten Eingabestring zurück
            return $inputString;
        }
    }
    public function clearNames(){
        $catNotifyId  = $this->ReadPropertyInteger("ParseNotifyCategoryID");
        $catAnalogId  = $this->ReadPropertyInteger("ParseAnalogCategoryID");
        $catAlarmId  = $this->ReadPropertyInteger("ParseAlarmCategoryID");

        if ($catNotifyId > 0){

            $catChilds = IPS_GetChildrenIDs($catNotifyId);

            foreach ($catChilds as $catChild) { //for each object in category
            

                $objchildids = IPS_GetChildrenIDs($catChild); //get variables of objects
                $parname = IPS_GetName($catChild);

                IPS_SetName($catChild,$this->getTextAfterLastSlash($parname));
            
            } 
        }
        if ($catAnalogId > 0){

            $catChilds = IPS_GetChildrenIDs($catAnalogId);

            foreach ($catChilds as $catChild) { //for each object in category
            

                $objchildids = IPS_GetChildrenIDs($catChild); //get variables of objects
                $parname = IPS_GetName($catChild);

                IPS_SetName($catChild,$this->getTextAfterLastSlash($parname));
            
            } 
        }
        if ($catAlarmId > 0){

            $catChilds = IPS_GetChildrenIDs($catAlarmId);

            foreach ($catChilds as $catChild) { //for each object in category
                $this->createAlarmprofile();

                $objchildids = IPS_GetChildrenIDs($catChild); //get variables of objects
                $parname = IPS_GetName($catChild);

                IPS_SetName($catChild,$this->getTextAfterLastSlash($parname));

                $config_arr = array(
                    "Type" => 0
                );
                $config_str = json_encode($config_arr);
                IPS_SetConfiguration($catChild, $config_str);
                IPS_ApplyChanges($catChild);


                foreach($objchildids as $child){
                    IPS_SetVariableCustomProfile($child, "Alarm");
                    IPS_SetVariableCustomAction($child, 1);
                }
            
            } 
        }
    }

    public function createAlarmprofile(){
        if(!IPS_VariableProfileExists ("Alarm") ){

            IPS_CreateVariableProfile("Alarm", 0);
            IPS_SetVariableProfileAssociation("Alarm", true, "Störung", "Alert", 0xFF0000);
            IPS_SetVariableProfileAssociation("Alarm", false, "OK", "Ok", 0x00FF00);

        }
    }

}
?>