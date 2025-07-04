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

        $this->RegisterPropertyBoolean("SendWhatsapp",false);
        $this->RegisterPropertyInteger("WhatappInst", 0);
        $this->RegisterPropertyString("WhatsappNumbers", "0159123456789,0157987456123");

        $this->RegisterPropertyBoolean("SendMail",false);
        $this->RegisterPropertyInteger("MailInst", 0);
        $this->RegisterPropertyString("MailAdresses", "hallo@mail.com,info@stoerung.de");

        $this->RegisterPropertyBoolean("SendFtp",false);
        $this->RegisterPropertyString("FtpHost", "your-server.de");
        $this->RegisterPropertyString("FtpUser", "YourUser");
        $this->RegisterPropertyString("FtpPassword", "YourPass");

        $this->RegisterPropertyBoolean("SendDiscord",false);
        $this->RegisterPropertyInteger("DiscordInst", 0);

        $this->RegisterPropertyBoolean("LogValues","true");

        $this->RegisterPropertyString("SslHttp","http://");
        $this->RegisterPropertyString("InfluxServer","172.16.9.106");
        $this->RegisterPropertyInteger("InfluxPort",8086);


        $this->RegisterPropertyInteger("UpdateintervallValues","30");
        $this->RegisterPropertyInteger("UpdateintervallAlarms","10");
        $this->RegisterPropertyInteger("UpdateintervallForceValues","30");
        $this->RegisterPropertyInteger("UpdateintervallSendFtp","30");


        $this->RegisterTimer("UpdateAlarms", 0, 'SEMS_checkAlarms('.$this->InstanceID.');');
        $this->RegisterTimer("UpdateValues", 0, 'SEMS_checkValues('.$this->InstanceID.',false);');
        $this->RegisterTimer("ForceUpdateValues", 0, 'SEMS_checkValues('.$this->InstanceID.',true);');
        $this->RegisterTimer("UpdateFtp", 0, 'SEMS_sendFtp('.$this->InstanceID.');');


        $this->RegisterTimer("CheckChanges", 0, 'SEMS_checkIfSomethingChanged('.$this->InstanceID.');');


        $this->RegisterVariableInteger("AlarmVarCount", "Number of Alarmvalues", "", 0);
        $this->RegisterAttributeInteger("AlarmVarCountOld", 0);

        $this->RegisterVariableInteger("AlarmActiveCount", "Number of Values in Alarmstate", "", 1);
        $this->RegisterVariableInteger("AnalogVarCount", "Number of Analogvalues", "", 2);
        $this->RegisterAttributeInteger("AnalogVarCountOld", 0);

        $this->RegisterVariableInteger("DigitalVarCount", "Number of DigitalValues", "", 3);
        $this->RegisterAttributeInteger("DigitalVarCountOld", 0);

        $this->RegisterVariableString("Alarmtable", "Alarmhistorie", "~HTMLBox", 30);
        $this->RegisterAttributeString("AtAlarmtable", "");



    }




    // Überschreibt die intere IPS_ApplyChanges($id) Funktion
    public function ApplyChanges() {

        $this->SetTimerInterval("UpdateAlarms", $this->ReadPropertyInteger("UpdateintervallAlarms") * 1000);
        $this->SetTimerInterval("UpdateValues", $this->ReadPropertyInteger("UpdateintervallValues") * 1000);
        $this->SetTimerInterval("ForceUpdateValues", $this->ReadPropertyInteger("UpdateintervallForceValues") * 1000 * 60);
        $this->SetTimerInterval("UpdateFtp", $this->ReadPropertyInteger("UpdateintervallSendFtp") * 1000);

        $this->SetTimerInterval("CheckChanges", 10 * 1000);

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

        $out = $ssl.$host.':'.$port.'/write?db='.$db.'';
        
        //IPS_LogMessage ("Write 2 Influx", "Output: ".$out);


        $ch = curl_init($out);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_POST,           1 );

        //IPS_LogMessage ("Write 2 Influx", "Posted Fields: ".$system.','.$category.'='.$valuename.' value=');
        //IPS_LogMessage ("Write 2 Influx", "Value: ".$value);


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



            $catAlarmId  = $this->ReadPropertyInteger("ParseAlarmCategoryID");
            $visuId = $this->ReadPropertyInteger("VisuId");

            $projectnumber = $this->ReadPropertyInteger("Projectnumber");
            $projectname = $this->ReadPropertyString("Projectname");
            $ispnumber = $this->ReadPropertyInteger("ISP");

           
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

                            
                            if ($this->notify($childid, $visuId, $catAlarmId, $projectnumber, $projectname, $ispnumber) == true){
                                    $inalarmcount ++;
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

    public function checkIfSomethingChanged()
    {
        $actAlarmCount = GetValueInteger($this->GetIDForIdent("AlarmVarCount"));
        $oldAlarmCount = $this->ReadAttributeInteger("AlarmVarCountOld");
        if($actAlarmCount <> $oldAlarmCount){
            $this->WriteAttributeInteger("AlarmVarCountOld", $actAlarmCount);
            $this->clearNames();
        }
        
        $actAnalogCount = GetValueInteger($this->GetIDForIdent("AnalogVarCount"));
        $oldAnalogCount = $this->ReadAttributeInteger("AnalogVarCountOld");
        if($actAnalogCount <> $oldAnalogCount){
            $this->WriteAttributeInteger("AnalogVarCountOld", $actAlarmCount);
            $this->clearNames();
        }

        $actDigitalCount = GetValueInteger($this->GetIDForIdent("DigitalVarCount"));
        $oldDigitalCount = $this->ReadAttributeInteger("DigitalVarCountOld");
        if($actDigitalCount <> $oldDigitalCount){
            $this->WriteAttributeInteger("DigitalVarCountOld", $actDigitalCount);
            $this->clearNames();
        }

    }
    public function checkValues($force) {

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
        

        $sendit         = $this->ReadPropertyBoolean("SendNotification");
        $sendwhatsapp   = $this->ReadPropertyBoolean("SendWhatsapp");
        $sendmail       = $this->ReadPropertyBoolean("SendMail");
        $senddc         = $this->ReadPropertyBoolean("SendDiscord");

        $idWhatsapp     = $this->ReadPropertyInteger("WhatappInst");
        $numbersWhatsapp     = $this->ReadPropertyString("WhatsappNumbers");

        $idMail         = $this->ReadPropertyInteger("MailInst");
        $mailAdresses    = $this->ReadPropertyString("MailAdresses");

        $idDc           = $this->ReadPropertyInteger("DiscordInst");

        $tdOld = $this->ReadAttributeString("AtAlarmtable");
        $td = $tdOld;

        $time = date("Y-m-d H:i:s");

                $art = $projectnumber." | ".$projectname. " | ISP ".$ispnr;
                $ico = "Flame";

            $smname = IPS_GetName(IPS_GetParent($trigid))."_".IPS_GetName($trigid);

            if (GetValue($trigid) == true && ($this->GetBuffer($smname) == "true"))  {
                
                $this->SetBuffer($smname, "false");

                if ($sendit == true){
                    VISU_PostNotification($webfrontid, $art, $smname, $ico, $targetid);
                } 
                

                if ($sendwhatsapp == true){

                    if ( $idWhatsapp > 0 && $idWhatsapp != 123456){

                        //IPS_LogMessage ("Whatsapp" .$projectnumber , "Senden gestartet");
                        $paramvals = [
                            "pn" => $art,
                            "stoertext" => $smname
                        ];
                        if ($numbersWhatsapp != ""){
                            $numbers = str_getcsv($numbersWhatsapp);
                            foreach ($numbers as $number){
                                WBM_SendMessageEx($idWhatsapp,$number,$paramvals);
                               // IPS_LogMessage ("Send Whatsapp " .$projectnumber , "Whatsapp an folgende Nummer gesendet: ". $number );
                            }
                        }else
                        {
                            WBM_SendMessage($idWhatsapp,$paramvals);
                            //IPS_LogMessage ("Send Whatsapp " .$projectnumber , "Whatsapp an konfigurierte Nummern gesendet");
                        }
                    }
                }

                if ($sendmail == true){
                    if ($idMail > 0 && $idMail != 123456){
                        //IPS_LogMessage ("Sendmail " .$projectnumber , "Mailing gestartet");

                        $adresses = str_getcsv($mailAdresses);

                        foreach ($adresses as $adress){
                            SMTP_SendMailEx($idMail, $adress, $art, $smname);
                         //   IPS_LogMessage ("Sendmail " .$projectnumber , "Mail an folgende Adresse gesendet: ".$adress );
                        }
                    }
                } 

                if ($senddc == true){
                    if ($idDc > 0 && $idDc != 123456){
                        DWM_SendMessage($idDc, $art, $smname);
                    }
                } 

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
            //IPS_LogMessage ("Notify", "Alarmhistory writen");

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

    public function sendFtp(){
        $goforit = $this->ReadPropertyBoolean("SendFtp");
        $ftp_server = $this->ReadPropertyString("FtpHost");
        $ftp_user_name = $this->ReadPropertyString("FtpUser");
        $ftp_user_pass = $this->ReadPropertyString("FtpPassword");

        if ($goforit == true or $goforit == 1){
            //IPS_LogMessage ("FTP", "FTP Is ON!!!");
            $catNotifyId  = $this->ReadPropertyInteger("ParseNotifyCategoryID");
            $catAnalogId  = $this->ReadPropertyInteger("ParseAnalogCategoryID");
            $catAlarmId   = $this->ReadPropertyInteger("ParseAlarmCategoryID");

            $ispName = "";
            $projectName = "";
            $yearName = "";

            $usedCat = 0;
            if ($catNotifyId > 0) $usedCat = $catNotifyId;
            elseif ($catAnalogId > 0) $usedCat = $catAnalogId;
            elseif ($catAlarmId > 0) $usedCat = $catAlarmId;

            if ($usedCat > 0){
                $parentid = IPS_GetParent($usedCat);
                $ispName = IPS_GetName($parentid);
                $projectId = IPS_GetParent($parentid);
                $projectName = IPS_GetName($projectId);
                $yearId = IPS_GetParent($projectId);
                $yearName = IPS_GetName($yearId);
            }

            $ftp = ftp_ssl_connect($ftp_server);
            if (!$ftp) {
                IPS_LogMessage ("FTP", "FTP-Verbindung fehlgeschlagen.");
                ///echo "FTP-Verbindung fehlgeschlagen.";
                return;
            }

            $login_result = ftp_login($ftp, $ftp_user_name, $ftp_user_pass);
            if (!$login_result) {
                IPS_LogMessage ("FTP", "FTP-Login fehlgeschlagen.");
                ///echo "FTP-Login fehlgeschlagen.";
                ftp_close($ftp);
                return;
            }

            // Pfad erstellen
            $ftpPath = "$yearName/$projectName/$ispName";
            ftp_mkdir($ftp, $ftpPath);
/*             $pathParts = explode("/", $ftpPath);
            $currentPath = "";
            foreach ($pathParts as $part) {
                $currentPath .= "$part/";
                if (!@ftp_chdir($ftp, $currentPath)) {
                    ftp_mkdir($ftp, $currentPath);
                }
            }
 */
            // Temp-Dateipfad vorbereiten

            ftp_chdir($ftp, $ftpPath);

            // Verarbeitungsfunktion
            $this->generateAndUploadCsv($catNotifyId, $ftp, "notify.csv", $projectName.$ispName."notify.csv");
            $this->generateAndUploadCsv($catAlarmId,  $ftp, "alarms.csv", $projectName.$ispName."alarms.csv");
            $this->generateAndUploadCsv($catAnalogId, $ftp, "analog.csv", $projectName.$ispName."analog.csv");

           // ftp_close($ftp);
        }
    }

    private function generateAndUploadCsv($catId, $ftp, $remotePath, $localPath) {
        if ($catId <= 0) return;

        $data = "Parentname;Childname;Value\n";

        //Objects in CAT!               //CAT
        $catChilds = IPS_GetChildrenIDs($catId); 
                            //Object
        foreach ($catChilds as $catChild) {
            //Variables of Object           //Singel CAT Object
            $objchildids = IPS_GetChildrenIDs($catChild); 
            $parentName = IPS_GetName($catChild);

            foreach ($objchildids as $varId) {

                if (IPS_VariableExists($varId)) {
                    $varName = IPS_GetName($varId);
                    $formattedValue = GetValueFormatted($varId);
                    $data .= "\"{$parentName}\";\"{$varName}\";\"{$formattedValue}\"\n";
                    
                    //IPS_LogMessage ("Filecontens", $data);
                }
            }
        }

        // Schreibe Datei lokal
        //$state = file_put_contents($localPath, $data);

        $path ='/var/lib/symcon/user/'.$localPath;
        $file = fopen($path, "w"); 
        $state = fwrite($file, $data); 
        fclose($file); 

        //IPS_LogMessage ("File Create", "File Put State/Size: ".$state); 

        // Lade hoch
        IPS_LogMessage("FTP", "Upload: local={$path}, remote={$remotePath}");
        $putstate = ftp_put($ftp, $remotePath, $path, FTP_BINARY);
        //IPS_LogMessage ("FTP", "FTP Put State: ".$putstate);
    }

}
?>