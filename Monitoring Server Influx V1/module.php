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
        $this->RegisterPropertyInteger("SqlId","27624");
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

        $this->RegisterPropertyBoolean("SendPhone",false);
        $this->RegisterPropertyString("PhoneNumbers", "0159123456789,0157987456123");

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
                         if(($changedtime > $time - $updatetime*10) | $force){

                            $parname = str_replace(" ","", $parname);
                            $varname = str_replace(" ","", $varname);
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
        
        $projectnumber = $this->ReadPropertyInteger("Projectnumber");
        $projectname = $this->ReadPropertyString("Projectname");
        $ispnumber = $this->ReadPropertyInteger("ISP");

        $sendit         = $this->ReadPropertyBoolean("SendNotification");
        $sendwhatsapp   = $this->ReadPropertyBoolean("SendWhatsapp");
        $sendphone      = $this->ReadPropertyBoolean("SendPhone");
        $sendmail       = $this->ReadPropertyBoolean("SendMail");
        $senddc         = $this->ReadPropertyBoolean("SendDiscord");
        $idSql          = $this->ReadPropertyInteger("SqlId");
        $idWhatsapp     = $this->ReadPropertyInteger("WhatappInst");
        $numbersWhatsapp     = $this->ReadPropertyString("WhatsappNumbers");
        $numbersPhone     = $this->ReadPropertyString("PhoneNumbers");
        $idMail         = $this->ReadPropertyInteger("MailInst");
        $mailAdresses    = $this->ReadPropertyString("MailAdresses");

        $idDc           = $this->ReadPropertyInteger("DiscordInst");
        $push           = 0;
        $tdOld = $this->ReadAttributeString("AtAlarmtable");
        $td = $tdOld;
        
        MySQL_Open($idSql);

        // === Helfer: Escaping für Strings ===
        if (function_exists('MySQL_RealEscapeString')) {
            $esc = fn(string $s) => MySQL_RealEscapeString($idSql, $s);
        } else {
        // Fallback, falls Modul-Funktion nicht verfügbar ist
            $esc = fn(string $s) => addslashes($s);
        }

        $time = date("Y-m-d H:i:s");

                $art = $projectnumber." | ".$projectname. " | ISP ".$ispnr;
                $ico = "Flame";

            $smname = IPS_GetName(IPS_GetParent($trigid))."_".IPS_GetName($trigid);

            if (GetValue($trigid) == true && ($this->GetBuffer($smname) == "true"))  {
                
                $this->SetBuffer($smname, "false");

                if ($sendit == true){
                    VISU_PostNotification($webfrontid, $art, $smname, $ico, $targetid);
                    $push = 1;
                } 
                
                $whatsapp   = 0;
                $whatsapped = 0;
                if ($sendwhatsapp == true){

                    if ( $idWhatsapp > 0 && $idWhatsapp != 123456){
                        $whatsapp = 1;
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
                            $whatsapped = 1;
                        }else
                        {
                            WBM_SendMessage($idWhatsapp,$paramvals);
                            //IPS_LogMessage ("Send Whatsapp " .$projectnumber , "Whatsapp an konfigurierte Nummern gesendet");
                            $whatsapped = 1;
                        }
                    }
                }

                $phonenumber  = '0';
                $phone        = 0;
                $phoned       = 0;

                if ($sendphone == true){
                    $phone = 1;
                    if ($numbersPhone != ""){

                        $phonenumbers = str_getcsv($numbersPhone);

                        foreach ($phonenumbers as $phonenumber){
                                           
                            $sqlcall = sprintf(
                                "INSERT INTO alarmcalls (
                                    alarmname, phonenumber
                                ) VALUES (
                                    '%s','%s'
                                )",
                                $esc($smname), $esc($phonenumber)
                            );

                            // === Ausführen ===
                            $okcall = MySQL_ExecuteSimple($idSql, $sqlcall);
                            if (!$okcall) {
                                echo "INSERT fehlgeschlagen.\n";
                            }

                            $phoned        = 1;
                        }
                    }
                }

                $mail          = 0;
                $mailed        = 0;
                $email_address = 'none';
                if ($sendmail == true){
                    if ($idMail > 0 && $idMail != 123456){
                        $mail = 1;
                        $adresses = str_getcsv($mailAdresses);
                        $email_address = $adresses;

                        foreach ($adresses as $adress){
                            SMTP_SendMailEx($idMail, $adress, $art, $smname);
                            $mailed        = 1;
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

                

                // Beispielwerte (ersetzen durch echte)
                $topic         = '';

                $sms        = 0;
                $smsnumber  = '0';
                $smsed      = 0;

                // === INSERT bauen ===
                $sql = sprintf(
                    "INSERT INTO alarms (
                        topic, projectnumber, projectname, isp, name,
                        sms, smsnumber, smsed,
                        whatsapp, wanumber, whatsapped,
                        phone, phonenumber, phoned,
                        mail, mailaddress, mailed,
                        push
                    ) VALUES (
                        '%s','%s','%s','%s','%s',
                        %d,'%s',%d,
                        %d,'%s',%d,
                        %d,'%s',%d,
                        %d,'%s',%d,
                        %d
                    )",
                    $esc($topic), $esc($projectnumber), $esc($projectname), $esc($ispnumber), $esc($smname),
                    (int)$sms, $esc($smsnumber), (int)$smsed,
                    (int)$whatsapp, $esc($numbersWhatsapp), (int)$whatsapped,
                    (int)$phone, $esc($numbersPhone), (int)$phoned,
                    (int)$mail, $esc($email_address), (int)$mailed,
                    (int)$push
                );

                // === Ausführen ===
                $ok = MySQL_ExecuteSimple($idSql, $sql);
                if (!$ok) {
                    echo "INSERT fehlgeschlagen.\n";
                }
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

public function sendFtp() {
    $goforit = $this->ReadPropertyBoolean("SendFtp");
    $ftp_server = $this->ReadPropertyString("FtpHost");
    $ftp_user_name = $this->ReadPropertyString("FtpUser");
    $ftp_user_pass = $this->ReadPropertyString("FtpPassword");

    if ($goforit !== true && $goforit !== 1) return;

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

    if ($usedCat > 0) {
        $parentid = IPS_GetParent($usedCat);
        $ispName = IPS_GetName($parentid);
        $projectId = IPS_GetParent($parentid);
        $projectName = IPS_GetName($projectId);
        $yearId = IPS_GetParent($projectId);
        $yearName = IPS_GetName($yearId);
    }

    $ftp = ftp_ssl_connect($ftp_server);
    if (!$ftp) {
        //IPS_LogMessage("FTP", "FTP-Verbindung fehlgeschlagen.");
        return;
    }

    $login_result = ftp_login($ftp, $ftp_user_name, $ftp_user_pass);
    if (!$login_result) {
        //IPS_LogMessage("FTP", "FTP-Login fehlgeschlagen.");
        ftp_close($ftp);
        return;
    }

    ftp_pasv($ftp, true); // Aktiviert passiven Modus

    // Remote-Verzeichnis erzeugen (rekursiv)
    $ftpPath = "$yearName/$projectName/$ispName";
    $parts = explode("/", $ftpPath);
    $currentPath = "";
    foreach ($parts as $part) {
        $currentPath .= "$part/";
        @ftp_mkdir($ftp, $currentPath); // ignoriert Fehler, wenn Verzeichnis schon existiert
    }

    $tmpDir = "/var/lib/symcon/user/";

    $this->generateAndUploadCsv($catNotifyId, $ftp, "$ftpPath/notify.csv",  $tmpDir.$projectName.$ispName."_notify.csv");
    $this->generateAndUploadCsv($catAlarmId,  $ftp, "$ftpPath/alarms.csv",  $tmpDir.$projectName.$ispName."_alarms.csv");
    $this->generateAndUploadCsv($catAnalogId, $ftp, "$ftpPath/analog.csv",  $tmpDir.$projectName.$ispName."_analog.csv");

    ftp_close($ftp);
}

private function generateAndUploadCsv($catId, $ftp, $remotePath, $localPath) {
    if ($catId <= 0) return;

    $data = "Parentname;Childname;Value\n";

    $catChilds = IPS_GetChildrenIDs($catId); 
    foreach ($catChilds as $catChild) {
        $objchildids = IPS_GetChildrenIDs($catChild); 
        $parentName = IPS_GetName($catChild);

        foreach ($objchildids as $varId) {
            if (IPS_VariableExists($varId)) {
                $varName = IPS_GetName($varId);
                $formattedValue = GetValueFormatted($varId);
                $data .= "\"{$parentName}\",\"{$varName}\",\"{$formattedValue}\"\n";
            }
        }
    }

    // Datei lokal schreiben
    $file = fopen($localPath, "w");
    $state = fwrite($file, $data);
    fclose($file);

    // Logging
    //IPS_LogMessage("FTP", "Lokaler Pfad: " . $localPath);
    //IPS_LogMessage("FTP", "Remote Pfad: " . $remotePath);
    //IPS_LogMessage("FTP", "Dateigröße lokal: " . filesize($localPath));

    // Upload
    $putstate = ftp_put($ftp, $remotePath, $localPath, FTP_BINARY);
    if ($putstate) {
      //IPS_LogMessage("FTP", "Upload OK: $remotePath");
    } else {
        //IPS_LogMessage("FTP", "Upload FEHLGESCHLAGEN: $remotePath");
    }
}


}
?>