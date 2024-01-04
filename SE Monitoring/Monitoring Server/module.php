<?
// Klassendefinition
class DiscordMessage extends IPSModule {
    // Überschreibt die interne IPS_Create($id) Funktion
    public function Create() {
        // Diese Zeile nicht löschen.
        parent::Create();
        
        $this->RegisterPropertyString("DiscordWsUrl","");
    }

    // Überschreibt die intere IPS_ApplyChanges($id) Funktion
    public function ApplyChanges() {
        // Diese Zeile nicht löschen
        parent::ApplyChanges();
    }
    /**
    * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
    * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
    *
    * DWM_SendMessage($id);
    *
    */
    public function SendMessage($topic,$message) {
   
            // Discord webhook URL
            $webhookUrl = $this->ReadPropertyString("DiscordWsUrl");

            // Data to send as JSON
            $data = array(
                "username" => $topic,
                "content" => $message
            );

            // Encode the data as JSON
            $jsonData = json_encode($data);

            // Initialize cURL session
            $ch = curl_init($webhookUrl);

            // Set cURL options
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "Content-Length: " . strlen($jsonData)
            ));

            // Execute the cURL session and capture the response
            $response = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                echo "cURL Error: " . curl_error($ch);
            }

            // Close cURL session
            curl_close($ch);

            // Display the response from the webhook
            ///echo "Webhook Response: " . $response;


    }
}
?>