<?php

// load library
//require_once __DIR__ . '/../libs/media.php';


// Klassendefinition
class RobonectWifiModul extends IPSModule
{

    /**
     * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
     * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
     *
     * ABC_MeineErsteEigeneFunktion($id);
     *
     */

    public function Create()
    {
        /* Create is called ONCE on Instance creation and start of IP-Symcon.
           Status-Variables und Modul-Properties for permanent usage should be created here  */
        parent::Create();

        // Connect MQTT Server
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
        // Properties Robonect Wifi Module
        $this->RegisterPropertyString("IPAddress", '0.0.0.0');
        $this->RegisterPropertyString("Username", '');
        $this->RegisterPropertyString("Password", '');
        $this->RegisterPropertyBoolean( "HTTPUpdateTimer", false );
        $this->RegisterPropertyString("MQTTTopic", '');
        $this->RegisterPropertyInteger( "UpdateTimer", 10 );
        $this->RegisterPropertyInteger( "MowingTime", 180 );
        $this->RegisterPropertyBoolean( "CameraInstalled", false );
        $this->RegisterPropertyBoolean( "StatusImage", false );
        $this->RegisterPropertyBoolean( "DateIamge", false );
        $this->RegisterPropertyInteger("TextColorImage", 350);
        $this->RegisterPropertyBoolean( "MediaElements", false );
        $this->RegisterPropertyBoolean( "HtmlBoxElements", false );
        $this->RegisterPropertyBoolean( "Webfrontend", false );

        $this->RegisterPropertyBoolean( "DebugLog", false );

        // Timer
        $this->RegisterTimer("ROBONECT_UpdateTimer", 0, 'ROBONECT_Update($_IPS[\'TARGET\']);');
        $this->RegisterTimer("ROBONECT_UpdateImageTimer", 0, 'ROBONECT_UpdateImage($_IPS[\'TARGET\']);');

        // Madia
        $this->RegisterPropertyInteger("ChartBatteryVoltageFill", 33023);
        $this->RegisterPropertyInteger("ChartBatteryVoltageLine", 160);
        $this->RegisterPropertyInteger("ChartBatteryCapacityFill", 16744576);
        $this->RegisterPropertyInteger("ChartBatteryCapacityLine", 16711680);
        $this->RegisterPropertyInteger("ChartBatteryChargingFill", 8421631);
        $this->RegisterPropertyInteger("ChartBatteryChargingLine", 8388863);
        $this->RegisterPropertyInteger("ChartBatteryTemperatureFill", 65280);
        $this->RegisterPropertyInteger("ChartBatteryTemperatureLine", 32768);
        
        // HTML Box Timer
        $this->RegisterPropertyInteger("TimerFontSize", 11);
        $this->RegisterPropertyInteger("TimerBackground", 16777215);
        $this->RegisterPropertyInteger("TimerWidth", 660);
        $this->RegisterPropertyBoolean("TimerHeader", false);
        $this->RegisterPropertyInteger("TimerHeaderSize", 25);
        $this->RegisterPropertyBoolean("TimerFooder", false);
        $this->RegisterPropertyInteger("TimerFooderSize", 25);
        $this->RegisterPropertyInteger("TimerFooderSpace", 5);
        $this->RegisterPropertyBoolean("TimerGrid", false);
        $this->RegisterPropertyInteger("TimerGridColor", 16777215);
        $this->RegisterPropertyBoolean("TimerSelect", false);
        $this->RegisterPropertyInteger("TimerSelectColor", 65280);
        $this->RegisterPropertyBoolean("TimerTimerText", false);
        $this->RegisterPropertyInteger("TimerTimerWidth", 60);
        $this->RegisterPropertyInteger("TimerTimerHigh", 25);
        $this->RegisterPropertyInteger("TimerTimer1", 5821431);
        $this->RegisterPropertyInteger("TimerTimer2", 95455);
        $this->RegisterPropertyInteger("TimerTimer3", 534922);
        $this->RegisterPropertyInteger("TimerTimer4", 6227124);
        $this->RegisterPropertyInteger("TimerTimer5", 13381370);
        $this->RegisterPropertyInteger("TimerTimer6", 16220632);
        $this->RegisterPropertyInteger("TimerTimer7", 16658020);
        $this->RegisterPropertyInteger("TimerTimer8", 14614842);
        $this->RegisterPropertyInteger("TimerTimer9", 16671790);
        $this->RegisterPropertyInteger("TimerTimer10", 16428120);
        $this->RegisterPropertyInteger("TimerTimer11", 16239662);
        $this->RegisterPropertyInteger("TimerTimer12", 10870528);
        $this->RegisterPropertyInteger("TimerTimer13", 308228);
        $this->RegisterPropertyInteger("TimerTimer14", 122740);
        // HTML Box Error
        $this->RegisterPropertyInteger("ErrorFontSize", 11);
        $this->RegisterPropertyBoolean("ErrorBackground", false);
        $this->RegisterPropertyInteger("ErrorBackgroundColor", 16777215);
        $this->RegisterPropertyInteger("ErrorRowHigh", 25);
        $this->RegisterPropertyInteger("ErrorDateWidth", 100);
        $this->RegisterPropertyInteger("ErrorHourWidth", 100);
        $this->RegisterPropertyInteger("ErrorWMessageidth", 100);
        // HTML Box Battery
        $this->RegisterPropertyInteger("BatteryFontSize", 12);
        $this->RegisterPropertyBoolean("BatteryBackground", false);
        $this->RegisterPropertyInteger("BatteryBackgroundColor", 16777215);
        $this->RegisterPropertyInteger("BatteryColumWidthNames", 160);
        $this->RegisterPropertyInteger("BatteryColumWidthValues", 90);
        $this->RegisterPropertyInteger("BatteryBarLength", 390);
        $this->RegisterPropertyInteger("BatteryBarHigh", 16);
        $this->RegisterPropertyInteger("BatteryBarBackground", 6710886);
        $this->RegisterPropertyInteger("BatteryBarPositivColor", 6591981);
        $this->RegisterPropertyInteger("BatteryBarNegativColor", 16711680);
        // HTML Box Betriebsstunden
        $this->RegisterPropertyInteger("OperHoursHeigh", 130);
        $this->RegisterPropertyInteger("OperHoursWidth", 650);
        $this->RegisterPropertyInteger("OperHoursHOffset", 20);
        $this->RegisterPropertyInteger("OperHoursBackgroundColor", 16777215);
        $this->RegisterPropertyInteger("OperHoursFieldBackgroundColor", 11316396);
        $this->RegisterPropertyInteger("OperHoursBarColor", 3054334);
        $this->RegisterPropertyInteger("OperHoursFontColor", 0);

    }

    public function ApplyChanges()
    {
        /* Called on 'apply changes' in the configuration UI and after creation of the instance */
        parent::ApplyChanges();

        // Generate Profiles & Variables
        $this->registerProfiles();
        $this->registerVariables();

        // Set Timer
        if ( $this->ReadPropertyBoolean( "HTTPUpdateTimer" ) and $this->ReadPropertyInteger("UpdateTimer") >= 10 ) {
            $this->SetTimerInterval("ROBONECT_UpdateTimer", $this->ReadPropertyInteger("UpdateTimer")*1000);
        } else {
            $this->SetTimerInterval("ROBONECT_UpdateTimer", 0 );
        }

        // Set CamTimer
        if ($this->ReadPropertyBoolean( "CameraInstalled" )) {
            $this->SetTimerInterval("ROBONECT_UpdateImageTimer", 30000);
        }
    }

    public function Update() {
        $semaphore = 'Robonect'.$this->InstanceID.'_Update';
        $this->log('Update - Try to enter Semaphore' );
        if ( IPS_SemaphoreEnter( $semaphore, 0 ) == false ) { return false; };
        $this->log('Update - Semaphore entered' );

        // HTTP status request
        $data = $this->executeHTTPCommand( 'status' );
        if ($data == false) {
            IPS_SemaphoreLeave( $semaphore );
            $this->log('Update - Semaphore leaved' );
            return false;
        } elseif ( isset( $data['successful'] ) ) {
            // set values to variables

            //--- Identification
            $this->updateIdent( "mowerName", $data['name'] );
            $this->updateIdent( "mowerSerial", $data['id'] );

            //--- Network
            $this->updateIdent( "mowerWlanStatus", $data['wlan']['signal'] );

            //--- Status
            $this->updateIdent( "mowerMode", $data['status']['mode'] );
            $this->updateIdent( "mowerStatus", $data['status']['status'] );
            $this->updateIdent( "mowerStopped", $data['status']['stopped'] );
            $this->updateIdent( "mowerStatusSinceDurationSec", $data['status']['duration'] );
            $this->updateIdent( "mowerMode",  $data['status']['mode'] );

            //--- Condition
            $this->updateIdent("mowerBatterySoc", $data['status']['battery'] );
            $this->updateIdent("mowerHours", $data['status']['hours'] );
            $this->updateIdent("mowerTemperature", $data['health']['temperature'] );
            $this->updateIdent("mowerHumidity", $data['health']['humidity']);
            if (isset($data['blades']['quality'])) {
                $this->updateIdent("mowerBladesQuality", $data['blades']['quality']);
            }
            if (isset($data['blades']['hours'])) {
                $this->updateIdent("mowerBladesOperatingHours", $data['blades']['hours']);
            }
            if (isset($data['blades']['days'])) {
                $this->updateIdent("mowerBladesAge", $data['blades']['days']);
            }

            //--- Timer
            $this->updateIdent("mowerTimerStatus", $data['timer']['status']);
            if ( isset( $data['timer']['next'] ) ) {
                $this->updateIdent("mowerNextTimerstart", $data['timer']['next']['unix'] );
            } else {
                $this->updateIdent("mowerNextTimerstart", 0 );
            }

            //--- Clock
            $this->updateIdent("mowerUnixTimestamp", $data['clock']['unix'] );
        }

        // Get Health Data
        $data = $this->executeHTTPCommand( 'health' );
        if ($data == false) {
            return false;
        } elseif ( isset( $data['successful'] ) ) {#
            $this->updateIdent("mowerVoltageInternal", $data['health']['voltages']['int3v3']/1000 );
            $this->updateIdent("mowerVoltageExternal", $data['health']['voltages']['ext3v3'] );
            $this->updateIdent("mowerVoltageBattery", $data['health']['voltages']['batt']/1000 );
        }

        // Set Timer
        if ( $this->ReadPropertyBoolean( "HTTPUpdateTimer" ) and $this->ReadPropertyInteger("UpdateTimer") >= 10 ) {
            $this->SetTimerInterval("ROBONECT_UpdateTimer", $this->ReadPropertyInteger("UpdateTimer")*1000);
        } else {
            $this->SetTimerInterval("ROBONECT_UpdateTimer", 0 );
        }

        if ( $this->ReadPropertyBoolean( "CameraInstalled" )) {
            $this->SetTimerInterval("ROBONECT_UpdateImageTimer", 60000, $this->UpdateImage());
        }

        IPS_SemaphoreLeave( $semaphore );
        $this->log('Update - Semaphore leaved' );
    }

    #================================================================================================
    public function Start() {
    #================================================================================================
    $Response = @$this->sendMQTT('/control', 'start');
        return $Response;
        // start the current modus of the lawnmower; tested
        // get data via HTTP Request
//        $data = $this->executeHTTPCommand( 'start' );
//        if ( $data == false ) {
//            return false;
//        } else {
//            return $data['successful'];
//        }
    }

    #================================================================================================
    public function Stop() {
    #================================================================================================
    $Response = @$this->sendMQTT('/control', 'stop');
        return $Response;
        // stop the current modus of the lawnmower; tested
        // get data via HTTP Request
//        $data = $this->executeHTTPCommand( 'stop' );
//        if ( $data == false ) {
//            return false;
//        } else {
//            return $data['successful'];
//        }
    }

    public function UpdateErrorList() {
        // $format = initial / "JSON" (return plain json)
        //           Array (returns encoded json)
        //           Http (returns a Http table)

        $semaphore = 'Robonect'.$this->InstanceID.'_ErrorList';
        if ( IPS_SemaphoreEnter( $semaphore, 0 ) == false ) { return false; };

        $data = $this->executeHTTPCommand( 'error' );
        if ( !isset( $data ) or  !isset( $data['errors'] ) or !isset( $data['successful'] ) or ( $data['successful'] != true ) ) { return false; }

        if ( count( $data['errors'] ) == 0 ) { return true; }

        $errorCount = count( $data['errors'] );
        $this->SetValue("mowerErrorCount", $errorCount );

        $errorListHTML = '<table>';
        $errorListHTML = $errorListHTML.'<colgroup>';
        $errorListHTML = $errorListHTML.'</colgroup>';
        $errorListHTML = $errorListHTML.'<thead><tr>';
        $errorListHTML = $errorListHTML.'<th></th>';
        $errorListHTML = $errorListHTML.'</thead>ID</tr>';
        $errorListHTML = $errorListHTML.'</thead>Datum</tr>';
        $errorListHTML = $errorListHTML.'</thead>Uhrzeit</tr>';
        $errorListHTML = $errorListHTML.'</thead>Fehlercode</tr>';
        $errorListHTML = $errorListHTML.'</thead>Beschreibung</tr>';
        $errorListHTML = $errorListHTML.'<tbody>';

        for ( $x = 0; $x < $errorCount; $x++  ) {
            $error = $data['errors'][$x];
            $colorCode = '#555555';
            if ( ($x % 2 ) != 0 ) $colorCode = '#333333';
            $errorListHTML = $errorListHTML.'<tr style="background-color:"'.$colorCode.'>';

            $index = $x+1;
            $errorListHTML = $errorListHTML.'<td>'.$index.'</td>';
            $errorListHTML = $errorListHTML.'<td>'.$error['date'].'</td>';
            $errorListHTML = $errorListHTML.'<td>'.$error['time'].'</td>';
            $errorListHTML = $errorListHTML.'<td>'.$error['error_code'].'</td>';
            $errorListHTML = $errorListHTML.'<td>'.$error['error_message'].'</td>';

            $errorListHTML = $errorListHTML.'</tr>';
        }

        $errorListHTML = $errorListHTML.'</tbody>';
        $errorListHTML = $errorListHTML.'</table>';

        $this->SetValue("mowerErrorList", $errorListHTML );

        IPS_SemaphoreLeave( $semaphore );
    }

    public function ClearErrors() {
        $data = $this->executeHTTPCommand('error&clear=1' );
        if ( $data == false ) {
            return false;
        } else {
            if ( $data['successful'] == true ) {
                $this->SetValue("mowerErrorcount", 0 );
            }
            return $data['successful'];
        }
    }

    #================================================================================================
    public function DriveHome() {
    #================================================================================================
        // Mower should drive to home position
        $Response = @$this->sendMQTT('/control/mode', 'home');
        return $Response;
 //        $data = $this->executeHTTPCommand('mode&mode=home' );
 //       if ( $data == false ) {
 //           return false;
 //       } else {
 //           return $data['successful'];
 //       }
    }

    public function SetService( string $Service ) {
        // Set Mode of the Mower

        // check parameter
        if ( $Service !== "reboot" && $Service !== "sleep" && $Service !== "shutdown") return false;
        $Response = @$this->sendMQTT('/control/mode', $Service);
        return $Response;
    }

    #================================================================================================
    public function SetMode( string $mode ) {
    #================================================================================================
        // check parameter
        if ( $mode !== "home" && $mode !== "eod" && $mode !== "man" && $mode !== "auto" ) return false;
        $Response = @$this->sendMQTT('/control/mode', $mode);
        return $Response;
//        $data = $this->executeHTTPCommand('mode&mode='.$mode );
//        if ( $data == false ) {
//            return false;
//        } else {
//            return $data['successful'];
//        }
    }

    #================================================================================================
    public function ReleaseDoor() {
    #================================================================================================
        // Bestätigen das Tor offen ist
        $Response = @$this->sendMQTT('/control/door',"release");
        return $Response;
    }

    public function StartMowingNow( int $duration ) {
        // start mowing now for XX minutes and go HOME afterwards (tested)
        $durationToRun = $duration;
        if ( $durationToRun == 0 ) {
            $durationToRun = $this->ReadPropertyInteger("MowingTime");
        }
        if ( $durationToRun > 1440 or $durationToRun < 10 ) return false;

        $start = date( 'H:i', time() );
        $data = $this->executeHTTPCommand('mode&mode=job&start='.$start.'&duration='.$durationToRun.'&after=home' );
        if ( $data == false ) {
            return false;
        } else {
            return $data['successful'];
        }
    }

    public function ScheduleJob( int $duration, string $modeAfter, string $start, string $stop ) {
        // remote start positions are not supported!

        // check parameters
        if ( $duration > 1440 or $duration < 60 ) return false;

        $modeParam = $modeAfter;
        if ( $modeParam == '' ) {
            // default HOME
            $modeParam = 'home';
        }
        if ( $modeParam !== "home" && $modeParam !== "eod" && $modeParam !== "man" && $modeParam !== "auto" ) return false;


        $startParam = $start;
        if ( $startParam == '' ) {
            // default now
            $startParam = date( 'H:i', time() );
        }
        if ( strlen( $startParam ) > 5 ) { return false; }
        if ( preg_match('((1[0-1]1[0-9]|2[0-3]):[0-5][0-9]?)', $startParam ) == false ) { return false; }

        $startIntValue = intval( substr( $startParam, 0, 2 ) ) * 60 + intval( substr( $startParam, 3, 2 ) );

        $stopParam = $stop;
        if ( $stopParam == '' ) {
            // default stop = start + duration + 2min
            $stopIntValue = $startIntValue + $duration + 5;
            $hour = intdiv( $stopIntValue, 60 );
            $minutes = $stopIntValue - ( $hour * 60 );
            if ( $hour > 23 ) { $hour = $hour - 24; };

            $stopParam = '';
            if ( $hour < 10 ) $stopParam = '0';
            $stopParam = $stopParam.$hour.':';
            if ( $minutes < 10 ) $stopParam = $stopParam.'0';
            $stopParam = $stopParam.$minutes;

        }
        if ( strlen( $stopParam ) > 5 ) { return false; }
        if ( preg_match('(([0-1][0-9]|2[0-3]):[0-5][0-9]?)', $stopParam ) == false ) { return false; }

        $stopIntValue = intval( substr( $stopParam, 0, 2 ) ) * 60 + intval( substr( $stopParam, 3, 2 ) );
        if ( $stopIntValue < $startIntValue ) { $stopIntValue = $stopIntValue + 1440; }
        // check duration is not longer than mowing interval
        if ( ( $stopIntValue - $startIntValue ) < $duration ) { return false; }


        //--- execute Command
        $data = $this->executeHTTPCommand('mode&mode=job&start='.$startParam.'&stop='.$stopParam.'&duration='.$duration.'&after='.$modeParam );
        if ( $data == false ) {
            return false;
        } else {
            return $data['successful'];
        }
    }

    #================================================================================================
    protected function GetBatteryData() {
    #================================================================================================
        $data = $this->executeHTTPCommand("battery");
        if ((!isset($data)) || (!$data['successful'])) {
            $this->log("Fehlermeldungen: ".$data);
            return false;
        }
        $this->SetValue("BatteryCharging", $data['batteries'][0]['current']);
        $this->SetValue("BatteryTemp", intval($data['batteries'][0]['temperature'])/10);
        $this->SetValue("BatteryCapacity", $data['batteries'][0]['capacity']['full']);
        $this->SetValue("BatteryRemaining", $data['batteries'][0]['capacity']['remaining']);
        $this->SetBatteryBox();
    }    

    public function GetTimerFromMower() {
        // reads all timer information and transfers it to an IPS Timer Instance

        define( 'WEEKDAYS', ['mo', 'tu', 'we', 'th', 'fr', 'sa', 'su'] );

        $data = $this->executeHTTPCommand( 'timer' );
        if ( !isset( $data ) or  !isset( $data['timer'] ) or !isset( $data['successful'] ) or ( $data['successful'] != true ) ) { return false; }

        if ( $this->GetIDForIdent('TimerPlanActive' ) == false ) { return false; }

        $TimerPlanActiveID = $this->GetIDForIdent('TimerPlanActive');
        $WochenplanEventID = @IPS_GetObjectIDByIdent( 'TimerWeekPlan'.$this->InstanceID, $TimerPlanActiveID );
        if ( $WochenplanEventID == false ) { return false; }

        // delete and re-create the ScheduleGroups to rebuild them from scratch
        IPS_SetEventScheduleGroup( $WochenplanEventID, 0, 0 );
        IPS_SetEventScheduleGroup( $WochenplanEventID, 1, 0 );
        IPS_SetEventScheduleGroup( $WochenplanEventID, 2, 0 );
        IPS_SetEventScheduleGroup( $WochenplanEventID, 3, 0 );
        IPS_SetEventScheduleGroup( $WochenplanEventID, 4, 0 );
        IPS_SetEventScheduleGroup( $WochenplanEventID, 5, 0 );
        IPS_SetEventScheduleGroup( $WochenplanEventID, 6, 0 );
        IPS_SetEventScheduleGroup( $WochenplanEventID, 0, 1);  // Mon
        IPS_SetEventScheduleGroup( $WochenplanEventID, 1, 2);  // Tue
        IPS_SetEventScheduleGroup( $WochenplanEventID, 2, 4);  // Wed
        IPS_SetEventScheduleGroup( $WochenplanEventID, 3, 8);  // Thu
        IPS_SetEventScheduleGroup( $WochenplanEventID, 4, 16); // Fri
        IPS_SetEventScheduleGroup( $WochenplanEventID, 5, 32);  // Sat
        IPS_SetEventScheduleGroup( $WochenplanEventID, 6, 64); // Sun
        IPS_SetEventScheduleGroupPoint( $WochenplanEventID, 0, 1, 0, 0, 0, 1);
        IPS_SetEventScheduleGroupPoint( $WochenplanEventID, 1, 1, 0, 0, 0, 1);
        IPS_SetEventScheduleGroupPoint( $WochenplanEventID, 2, 1, 0, 0, 0, 1);
        IPS_SetEventScheduleGroupPoint( $WochenplanEventID, 3, 1, 0, 0, 0, 1);
        IPS_SetEventScheduleGroupPoint( $WochenplanEventID, 4, 1, 0, 0, 0, 1);
        IPS_SetEventScheduleGroupPoint( $WochenplanEventID, 5, 1, 0, 0, 0, 1);
        IPS_SetEventScheduleGroupPoint( $WochenplanEventID, 6, 1, 0, 0, 0, 1);

        $timerList = $data['timer'];
        for ( $x = 0; $x<=13; $x++ ) {

            $timer = $timerList[$x];
            if ( $timer['enabled'] == true ) {

                for ( $weekday = 0; $weekday <= 6; $weekday = $weekday + 1 ) {
                    if ( $timer['weekdays'][WEEKDAYS[$weekday]] ) {
                        $startHour = intval( substr( $timer['start'], 0, 2 ) );
                        $startMinutes = intval( substr( $timer['start'], 3, 2 ) );
                        IPS_SetEventScheduleGroupPoint( $WochenplanEventID, $weekday, 2, $startHour, $startMinutes, 0, 2 );
                        $stopHour = intval( substr( $timer['end'], 0, 2 ) );
                        $stopMinutes = intval( substr( $timer['end'], 3, 2 ) );
                        IPS_SetEventScheduleGroupPoint( $WochenplanEventID, $weekday, 3, $stopHour, $stopMinutes, 0, 1 );
                    }
                }

            }
        }

        return $data;
    }

    public function SetTimerToMower() {
        // transfer the wekk plan timer to the mower
        // Wochenplan auslesen

        $TimerPlanActiveID = $this->GetIDForIdent("TimerPlanActive");

        if ( $TimerPlanActiveID == false ) {
            return false;
        }
        $TimerPlanActiveID = $this->GetIDForIdent('TimerPlanActive');
        $WochenplanEventID = @IPS_GetObjectIDByIdent( 'TimerWeekPlan'.$this->InstanceID, $TimerPlanActiveID );
        if ( $WochenplanEventID === false ) {
            return;
        }
        $Wochenplan = IPS_GetEvent($WochenplanEventID);

        $listOfWeekdays = array("mo","tu","we","th","fr","sa","su");
        $emptyWeekdays = json_decode( '{"mo": false, "tu": false, "we": false, "th": false, "fr": false, "sa": false, "su": false}', true );
        $emptyTimer = json_decode( '{"id": 0, "enabled": false, "start": "08:00", "end": "18:00", "weekdays": {"mo": false, "tu": false, "we": false, "th": false, "fr": false, "sa": false, "su": false}}', true);
        $timerID = 1;
        $timerList = [];

        // über die Schedule-Gruppen (Tage) loopen
        if ( isset( $Wochenplan ) and isset( $Wochenplan['ScheduleGroups'] ) ) {
            for( $tag = 0; $tag <= 6; $tag = $tag + 1 ) {
                // Montag (Tag 0) - Sonntag (Tag 6)

                if ( isset( $Wochenplan['ScheduleGroups'][$tag] ) and
                    isset( $Wochenplan['ScheduleGroups'][$tag]['Points'] ) and
                    count($Wochenplan['ScheduleGroups'][$tag]['Points'] ) > 0 ) {
                    $maehenGestartet = false;
                    $StartZeit = "";

                    for( $x = 0; $x < count($Wochenplan['ScheduleGroups'][$tag]['Points']); $x++ ) {
                        if ( ( $maehenGestartet == false ) and ( $Wochenplan['ScheduleGroups'][$tag]['Points'][$x]['ActionID'] == 2 ) ) {
                            // ActionID = 2 => mähenStarten; nur, wenn maehen nicht gestartet einen neuen Timer planen
                            $timer['id']       = $timerID;
                            $timer['enabled']  = true;
                            $timer['start']    = '';
                            $timer['end']      = '';
                            $timer['weekdays'] = [];

                            $StartStunde = $Wochenplan['ScheduleGroups'][$tag]['Points'][$x]['Start']['Hour'];
                            if ( strlen( $StartStunde ) == 1 ) $StartStunde = '0'.$StartStunde;
                            $StartMinute = $Wochenplan['ScheduleGroups'][$tag]['Points'][$x]['Start']['Minute'];
                            if ( strlen( $StartMinute ) == 1 ) $StartMinute = '0'.$StartMinute;
                            $StartZeit = $StartStunde.":".$StartMinute;
                            $timer['start'] = $StartZeit;
                            $timer['weekdays'] = $emptyWeekdays;
                            $timer['weekdays'][$listOfWeekdays[$tag]] = true;
                            $maehenGestartet = true;
                            continue;
                        }
                        if ( ( $maehenGestartet == true ) and ( $Wochenplan['ScheduleGroups'][$tag]['Points'][$x]['ActionID'] == 1 ) ) {
                            // ActionID = 1 => mähenBeenden; nur, wenn maehen gestartet das Event beenden

                            $StopStunde = $Wochenplan['ScheduleGroups'][$tag]['Points'][$x]['Start']['Hour'];
                            if ( strlen( $StopStunde ) == 1 ) $StopStunde = '0'.$StopStunde;
                            $StopMinute = $Wochenplan['ScheduleGroups'][$tag]['Points'][$x]['Start']['Minute'];
                            if ( strlen( $StopMinute ) == 1 ) $StopMinute = '0'.$StopMinute;

                            $StopZeit = $StopStunde.":".$StopMinute;
                            if ( $StopZeit == '00:00' ) $StopZeit = '23:59';

                            $timer['end'] = $StopZeit;

                            // prüfen, ob der Timer neu ist, oder ein bereits vorhandener genutzt werden kann
                            for ($y = 0; $y < count( $timerList ); $y++ ) {
                                if ( $timerList[$y]['start'] == $timer['start'] and
                                    $timerList[$y]['end']   == $timer['end'] ) {
                                    // Timer passt, also Wochentag hinzufügen und neue Timer verwerfen
                                    $timerList[$y]['weekdays'][$listOfWeekdays[$tag]] = true;
                                    $timer['id'] = '';
                                }
                            }

                            if ( $timer['id'] == $timerID ) {
                                // neuer Timer
                                array_push( $timerList, $timer);
                                $timerID++;
                            }

                            $maehenGestartet = false;
                            continue;
                        }
                    }
                }

                if ( $maehenGestartet == true ) {
                    // eine Mähung fängt vor 0:00 an und hört auf 0:00 auf => kein stop mähen an dem Tag!
                    $timer['end'] = '23:59';
                    // prüfen, ob der Timer neu ist, oder ein bereits vorhandener genutzt werden kann
                    for ($y = 0; $y < count( $timerList ); $y++ ) {
                        if ( $timerList[$y]['start'] == $timer['start'] and
                            $timerList[$y]['end']   == $timer['end'] ) {
                            // Timer passt, also Wochentag hinzufügen und neue Timer verwerfen
                            $timerList[$y]['weekdays'][$listOfWeekdays[$tag]] = true;
                            $timer['id'] = '';
                        }
                    }

                    if ( $timer['id'] == $timerID ) {
                        // neuer Timer
                        array_push( $timerList, $timer);
                        $timerID++;
                    }

                    $maehenGestartet = false;
                }
            }
        };

        $missingTimers = 14-count( $timerList );
        for( $z=0; $z <= $missingTimers; $z++ ) {
            $timer = $emptyTimer;
            $timer['id'] = $timerID;
            $timerID++;
            array_push( $timerList, $timer );
        }

        // Robonect Programmieren
        $success = true;
        for ( $x = 0; $x <= 13; $x++ ) {

            $cmd = 'timer&timer='.$timerList[$x]['id'].'&start='.$timerList[$x]['start'].'&end='.$timerList[$x]['end'];
            for ( $tag = 0; $tag <= 6; $tag++ ) {
                if ( $timerList[$x]['weekdays'][$listOfWeekdays[$tag]] == true ) {
                    $cmd = $cmd.'&'.$listOfWeekdays[$tag].'=1';
                } else {
                    $cmd = $cmd.'&'.$listOfWeekdays[$tag].'=0';
                }
            }
            if ( $timerList[$x]['enabled'] == true ) {
                $cmd = $cmd."&enable=1";
            } else {
                $cmd = $cmd."&enable=0";
            }

            // Kommando senden
            $success = $success and $this->executeHTTPCommand( $cmd );
        }

        return $success;
    }

    #================================================================================================
    protected function executeHTTPCommand( $command ) {
    #================================================================================================
        $IPAddress = trim($this->ReadPropertyString("IPAddress"));
        $Username = trim($this->ReadPropertyString("Username"));
        $Password = trim($this->ReadPropertyString("Password"));
        $this->log('executeHTTPCommand - Start');
        // check if IP is ocnfigured and valid
        if ($IPAddress == "0.0.0.0") {
            $this->SetStatus(200); // no configuration done
            return false;
        } elseif (filter_var($IPAddress, FILTER_VALIDATE_IP) == false) {
            $this->SetStatus(201); // no valid IP configured
            return false;
        }
        $this->log('Http Request send');
        switch ($command) {
            case 'cam' :
                $URL = 'http://' . $IPAddress . '/cam.jpg';
                try {
                    $ch = curl_init();
                    curl_setopt_array($ch, [
                        CURLOPT_URL => $URL,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                        CURLOPT_USERPWD => $Username . ':' . $Password,
                        CURLOPT_TIMEOUT => 30
                    ]);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    if( ! $data = curl_exec($ch)) {
                        $this->log ((curl_error($ch)));
                        curl_close($ch);
                        return false;
                    }
                    curl_close($ch);
                    $this->log('Http Request finished');
                } catch (Exception $e) {
                    curl_close($ch);
                    $this->log('Http Request on error');
                    $this->SetStatus(500); // Server Error
                    return false;
                };
                return $data;
                break;
            case '' : 
                return false;
                break;
            default :
                $URL = 'http://' . $IPAddress . '/json?cmd=' . $command;
                try {
                    $ch = curl_init();
                    curl_setopt_array($ch, [
                        CURLOPT_URL => $URL,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                        CURLOPT_USERPWD => $Username . ':' . $Password,
                        CURLOPT_TIMEOUT => 30
                    ]);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    //  $json = curl_exec($ch);
                    if( ! $json = curl_exec($ch)) {
                        $this->log ((curl_error($ch)));
                        curl_close($ch);
                        return false;
                    }
                    curl_close($ch);
                    $this->log('Http Request finished');
                } catch (Exception $e) {
                    curl_close($ch);
                    $this->log('Http Request on error');
                    $this->SetStatus(203); // no valid IP configured
                    return false;
                };
                if (strlen($json) > 3) {
                  $this->SetStatus(102); // Robonect found
                } else {
                  $this->SetStatus(202); // No Device at IP
                }
                $this->log("Response: ".$json);
                return json_decode( utf8_encode($json), true, 1000, JSON_INVALID_UTF8_IGNORE);
                break;
        }
        return false;
    }

    public function RequestAction($Ident, $Value)
    {

        switch ($Ident) {
            case "mowerModeInteractive":
                switch( $Value ) {
                    case 0: // manuell
                        if ( $this->SetMode( 'man' ) ) {
                            $this->SetValue("mowerModeInteractive", $Value );
                        }
                        break;
                        break;
                    case 1: // Timer auto
                        if ( $this->SetMode( 'auto' ) ) {
                            $this->SetValue("mowerModeInteractive", $Value );
                        }
                        break;
                }
                break;

            case "manualAction":
                switch( $Value ) {
                    case 0: // jetzt mähen
                        if ($this->StartMowingNow(0) ) {
                            $this->SetValue("manualAction", $Value);
                        }
                        break;

                    case 1: // pause / weitermachen
                        // wir müssen zunächst sicher sein, das "manuell gestoppt" sauber sitzt
                        $this->Update();
                        if ( GetValueBoolean($this->GetIDForIdent('mowerStopped')) == false ) {
                            if ( $this->Stop() ) {
                                $this->SetValue("manualAction", 1);
                            }
                        } else {
                            if ( $this->Start() ) {
                                $this->SetValue("manualAction", -1);
                            }
                        }
                        $this->Update(); // Werte neu ermitteln
                        break;

                    case 2: // mähen beenden
                        if ( $this->SetMode( 'home' ) ) {
                            $this->SetValue("manualAction", $Value );
                        }
                        $this->Update();
                        break;
                }
                break;
            case 'timerTransmitAction':
                switch( $Value ) {
                    case 0: // vom Robonect lesen
                        $this->GetTimerFromMower();
                        break;
                    case 1: // zum Robonect übertragen
                        $this->SetTimerToMower();
                        break;
                }
                break;
        }
    }

    public function ReceiveData($JSONString) {
        // MQTT Tropics
        $topicList['/device/name']['Ident']                 = 'mowerName';
        $topicList['/device/serial']['Ident']               = 'mowerSerial';

        $topicList['/mower/status']['Ident']                = 'mowerStatus';
        $topicList['/mower/mode']['Ident']                  = 'mowerMode';

        $topicList['/door/open']['Ident']                   = 'doorStatus';

        $topicList['/mower/mode']['Ident']                  = 'mowerMode';
        $topicList['/mower/status']['Ident']                = 'mowerStatus';
        $topicList['/mower/status/plain']['Ident']          = 'mowerStatusPlain';
        $topicList['/mower/substatus']['Ident']             = 'mowerSubstatus';
        $topicList['/mower/substatus/plain']['Ident']       = 'mowerSubstatusPlain';
        $topicList['/mower/stopped']['Ident']               = 'mowerStopped';
        $topicList['/mower/status/duration']['Ident']       = 'mowerStatusSinceDurationMin';

        $topicList['/mower/battery/charge']['Ident']        = 'mowerBatterySoc';
        $topicList['/health/climate/temperature']['Ident']  = 'mowerTemperature';
        $topicList['/health/climate/humidity']['Ident']     = 'mowerHumidity';
        $topicList['/health/voltage/batt']['Ident']         = 'mowerVoltageBattery';
        $topicList['/health/voltage/int33']['Ident']        = 'mowerVoltageInternal';
        $topicList['/health/voltage/ext33']['Ident']        = 'mowerVoltageExternal';
        $topicList['/mower/statistic/hours']['Ident']       = 'mowerHours';
        $topicList['/wlan/rssi']['Ident']                   = 'mowerWlanStatus';
        $topicList['/mqtt']['Ident']                        = 'mowerMqttStatus';
        $topicList['/mower/error/code']['Ident']            = 'mowerErrorCount';
        $topicList['/mower/error/message']['Ident']         = 'mowerErrorMessage';
        $topicList['/mower/blades/quality']['Ident']        = 'mowerBladesQuality';
        $topicList['/mower/blades/hours']['Ident']          = 'mowerBladesOperatingHours';
        $topicList['/mower/blades/days']['Ident']           = 'mowerBladesAge';

        $topicList['/weather/data/break']['Ident']          = 'WeatherBreak';
        $topicList['/weather/data/humidity']['Ident']       = 'WeatherHumidity';
        $topicList['/weather/data/rain']['Ident']           = 'WeatherRain';
        $topicList['/weather/data/temperature']['Ident']    = 'WeatherTemperature';
        $topicList['/weather/data/service']['Ident']        = 'WeatherService';

        $topicList['/mower/timer/next/unix']['Ident']       = 'mowerNextTimerstart';

        if ( $JSONString == '' ) {
            $this->log('No JSON' );
            return true;
        }
		$this->SendDebug("Received", $JSONString, 0);
        $Data = json_decode( $JSONString);
        // Prüfen ob alles OK ist
        if ($Data === false or $Data->DataID != '{7F7632D9-FA40-4F38-8DEA-C83CD4325A32}') {
            $this->SendDebug("nvalid Parent", KL_ERROR, 0);
            $this->log('Invalid Parent' );
            return true;
        }

        $mqttTopic = $this->ReadPropertyString("MQTTTopic");
        if ( ( $mqttTopic == "" ) or ( strpos( $Data->Topic, $mqttTopic.'/' ) === false ) ) {
            $this->SendDebug("Bad Topic", $Data->Topic, 0);
            $this->log('Bad Topic');
             return true;
        }

        $topic = substr( $Data->Topic, strlen( $mqttTopic), (strlen($Data->Topic) - strlen($mqttTopic)));

        $this->log('Topic: '.$topic. ', Payload: '.$Data->Payload);

        if ( isset( $topicList[$topic] ) ) {
            $this->log('Try to update data ' . $topicList[$topic]['Ident'] . ' with ' . $Data->Payload);
            $this->updateIdent($topicList[$topic]['Ident'], $Data->Payload);
            if ($topicList[$topic]['Ident'] != 'mowerMqttStatus') {
                $this->SetValue("mowerMqttStatus", 1); // online
            }
        // Timer Topic
        //        elseif (str_contians($topic, 'timer')) { php 8
        } elseif (strpos($topic, 'timer') !== false) {
            // Existiert die Timer Kategorie
            if (!$TimerCat = @IPS_GetCategoryIDByName('Timers', $this->InstanceID)) {
                $this->log("Missing Timers Category!");
                return;
            }
            // /mower/timer/ch0/enable
            if (!$Buffer = @IPS_GetObjectIDByIdent("TimerBuffer", $TimerCat)) {
                $this->log("Timer Buffer Variable not found!");
            }
            // Topc kürzen auf Timer Kanal und spliten auf Kanal und Wert
            list ($TimerChannel, $TimerValue) = explode('/', str_replace('/mower/timer/', '', $topic));
            // Kanal in integer umwandel
            $TimerChannel = intval(str_replace('ch', '', $TimerChannel));
            $TimerChannel++;
            /*
            if ($TimerChannel < 10) {
                $TimeVariableName = "Timer0".$TimerChannel.$TimerValue;
            } else {
                $TimeVariableName = "Timer".$TimerChannel.$TimerValue;
            }
            */
            $TimerVariableName = "Timer".$TimerChannel.$TimerValue;
            if (!$TimerVariableID = @IPS_GetObjectIDByIdent($TimerVariableName, $TimerCat)) {
                $this->log("Timer Variable not found!");
                return;
            }
            SetValue($TimerVariableID, $Data->Payload);
            $this->SetTimerBox(null);
        } else {
            $this->log('Unkown Topic: '.$topic. ', Payload: '.$Data->Payload );
        }

    }

    protected function updateIdent( string $ident, $payload ) {

        switch ( $ident ) {
            case 'mowerName':
                $this->SetValue("mowerName", $payload);
                break;
            case 'mowerSerial':
                $this->SetValue("mowerSerial", $payload);
                break;


            case 'mowerMode':
                $this->SetValue("mowerMode", $payload);
                if ( $payload == 0 ) {
                    $this->SetValue("mowerModeInteractive", 1); // automatisch = Timer
                } else {
                    $this->SetValue("mowerModeInteractive", 0); // sonst = manuell
                }
                break;
            case 'doorStatus':
                $this->SetValue("doorStatus", $payload);
                break;
            case 'mowerStatus':
                if (($payload == 2) || ($payload == 3) || ($payload == 5) || ($payload == 7) || ($payload == 8)) {
                    $this->SetTimerInterval("ROBONECT_UpdateImageTimer", 60000);
                }
                $this->SetValue("mowerStatus", $payload);
                break;
            case 'mowerStatusPlain':
                $this->SetValue("mowerStatusPlain", $payload);
                break;
            case 'mowerSubstatus':
                $this->SetValue("mowerSubstatus", $payload);
                break;
            case 'mowerSubstatusPlain':
                $this->SetValue("mowerSubstatusPlain", $payload);
                break;
            case 'mowerStopped':
                $this->SetValue("mowerStopped", $payload);
                if (($payload == false) and (GetValueInteger($this->GetIDForIdent("manualAction")) == 2)) {
                    // "Pause" als Aktion gehighlighted, aber Mäher nicht gestoppt
                    $this->SetValue("manualAction", -1); // keine Aktion im Webfront gehighlighted
                } elseif ($payload == true) {
                    $this->SetValue("manualAction", 1); // "Pause" Aktion highlighten
                }
                break;
            case 'mowerStatusSinceDurationSec':
                $durationSince = 0+filter_var($payload, FILTER_SANITIZE_NUMBER_INT);
                $statusSinceTimestamp = time() - $durationSince;
                $this->LogMessage( 'Duration: '.$durationSince.' Timestamp: '.$statusSinceTimestamp, KL_DEBUG );
                $this->SetValue("mowerStatusSince", $statusSinceTimestamp );
                if (intdiv($durationSince, 86400) > 0) {
                    $Text = intdiv($durationSince, 86400) . ' Tag';
                    if (intdiv($durationSince, 86400) > 1) $Text = $Text . 'en';
                } else {
                    $Text = "";
                    if (intdiv($durationSince, 3600) > 0) $Text = intdiv($durationSince, 3600) . " Stunden ";
                    $Text = $Text . date("i", $durationSince) . " Minuten";
                }
                $this->SetValue("statusSinceDescriptive", $Text);
                break;
            case 'mowerStatusSinceDurationMin':
                $durationSince = 0+filter_var($payload, FILTER_SANITIZE_NUMBER_INT);
                $statusSinceTimestamp = time() - $durationSince*60; // substract seconds
                $this->log('Duration: '.$durationSince.' Timestamp: '.$statusSinceTimestamp );
                $this->SetValue("mowerStatusSince", $statusSinceTimestamp );
                $duration = $durationSince*60;
                if (intdiv($duration, 86400) > 0) {
                    $Text = intdiv($duration, 86400) . ' Tag';
                    if (intdiv($duration, 86400) > 1) $Text = $Text . 'en';
                } else {
                    $Text = "";
                    if (intdiv($duration, 3600) > 0) $Text = intdiv($duration, 3600) . " Stunden ";
                    $Text = $Text . date("i", $duration) . " Minuten";
                }
                $this->SetValue("statusSinceDescriptive", $Text);
                break;
            case 'mowerStatusSinceTimestamp':
                $statusSinceTimestamp = $payload;
                $difference = ( time() - $payload) / 60;
                $this->SetValue("mowerStatusSince", $statusSinceTimestamp );
                if (intdiv($difference, 86400) > 0) {
                    $Text = intdiv($difference, 86400) . ' Tag';
                    if (intdiv($difference, 86400) > 1) $Text = $Text . 'en';
                } else {
                    $Text = "";
                    if (intdiv($difference, 3600) > 0) $Text = intdiv($difference, 3600) . " Stunden ";
                    $Text = $Text . date("i", $payload) . " Minuten";
                }
                $this->SetValue("statusSinceDescriptive", $Text);
                break;



            case 'mowerBatterySoc':
                $this->SetValue("mowerBatterySoc", $payload );
                $this->GetBatteryData();
                break;
            case 'mowerVoltageBattery':
                $this->SetValue("mowerVoltageBattery", $payload );
                break;
            case 'mowerVoltageInternal':
                $this->SetValue("mowerVoltageInternal", $payload );
                break;
            case 'mowerVoltageExternal':
                $this->SetValue("mowerVoltageExternal", $payload );
                break;
            case 'mowerHours':
                $this->SetValue("mowerHours", $payload );
                break;
            case 'mowerWlanStatus':
                $WLANIntensity = 100;
                $WLANmDB = 0+filter_var($payload, FILTER_SANITIZE_NUMBER_INT);
                if (abs($WLANmDB) >= 95) {
                    $WLANIntensity = 0;
                } else {
                    $WLANIntensity = min(max(round(((95 - abs($WLANmDB)) / 60) * 100, 0), 0), 100);
                }
                $this->SetValue("mowerWlanStatus", $WLANIntensity);
                break;
            case 'mowerMqttStatus':
                switch ( $payload ) {
                    case 'online':
                        $this->SetValue("mowerMqttStatus", 1 );
                        break;
                    default:
                        $this->SetValue("mowerMqttStatus", 0 );
                        break;
                }
                break;
            case 'mowerTemperature':
                $this->SetValue("mowerTemperature", $payload );
                break;
            case 'mowerHumidity':
                $this->SetValue("mowerHumidity", $payload );
                break;
            case 'mowerErrorCount':
                $this->SetValue("mowerErrorCount", $payload );
                $this->SetErrorBox();
                break;                
            case 'mowerErrorMessage':
                $this->SetValue("mowerErrorMessage", $payload );
                break;
            case 'mowerBladesQuality':
                $this->SetValue("mowerBladesQuality", $payload );
                break;
            case 'mowerBladesOperatingHours':
                $this->SetValue("mowerBladesOperatingHours", $payload );
                break;
            case 'mowerBladesAge':
                $this->SetValue("mowerBladesAge", $payload );
                break;


            case 'mowerTimerStatus':
                $this->SetValue( "mowerTimerStatus", $payload );
                break;
            case 'mowerNextTimerstart':
                if ( $payload == 0 ) {
                    $this->SetValue("mowerNextTimerstart", 0 );
                } else {
                    $unixTimestamp = $payload;
                    $dateTimeZoneLocal = new DateTimeZone(date_default_timezone_get());
                    $localTime = new DateTime("now", $dateTimeZoneLocal);
                    $unixTimestamp = $unixTimestamp - $dateTimeZoneLocal->getOffset($localTime);
                    $this->SetValue("mowerNextTimerstart", $unixTimestamp);
                }
                break;

                case 'WeatherTemperature':
                    $this->SetValue("WeatherTemperature", $payload );
                    break;
                case 'WeatherHumidity':
                    $this->SetValue("WeatherHumidity", $payload );
                    break;
                case 'WeatherBreak':
                    $this->SetValue("WeatherBreak", $payload );
                    break;
                case 'WeatherRain':
                    $this->SetValue("WeatherRain", $payload );
                    break;
                case 'WeatherService':
                    $this->SetValue("WeatherService", $payload );
                    break;


            case 'mowerUnixTimestamp':
                $unixTimestamp = $payload;
                $dateTimeZoneLocal = new DateTimeZone(date_default_timezone_get());
                $localTime = new DateTime("now", $dateTimeZoneLocal);
                $unixTimestamp = $unixTimestamp - $dateTimeZoneLocal->getOffset($localTime);
                $this->SetValue("mowerUnixTimestamp", $unixTimestamp );
                break;

        }

    }

    #================================================================================================
    public function UpdateImage () {
    #================================================================================================
        $semaphore = 'Robonect'.$this->InstanceID.'_Update';
        if ( IPS_SemaphoreEnter( $semaphore, 0 ) == false ) { 
            $this->log('UpdateImage - No semaphore entered' );
            return false; 
        }
        $this->log('UpdateImage - Semaphore entered' );
        $media_file =  'media/' . 'Cam.' . $this->InstanceID . '.jpg';

        if (!$media_id = @IPS_GetMediaIDByFile($media_file)) {
            return false;
       }

        // update media content
        $filename = IPS_GetKernelDir() . $media_file;
        $fileContent = $this->executeHTTPCommand('cam');
        if (!$fileContent) {
            $this->log('UpdateImage - Keine Gültige Antwort vom Server !!!');
            return false;
        }
        $result = file_put_contents($filename, $fileContent);
        if (!$result) {
            $this->log( 'UpdateImage - Fehler beim schreiben des Bildes '.$filename);
            return false;
        }
        // Bild Overlay erstellen
        $source = imagecreatefromjpeg($filename);
        if($this->ReadPropertyBoolean("StatusImage") or $this->ReadPropertyBoolean("DateIamge")) {
            $TextStatus = $this->GetValue ("mowerStatusPlain");
            $col = $this->ReadPropertyInteger("TextColorImage");
            if ($this->ReadPropertyBoolean("StatusImage") and $this->ReadPropertyBoolean("DateIamge")) {
                imagestring($source, 4, 5, 460, date("d.m.Y H:i:s")." | Status: ".utf8_decode($TextStatus), $col);
            } elseif($this->ReadPropertyBoolean("DateIamge")) {
                imagestring($source, 4, 5, 460, date("d.m.Y H:i:s"), $col);
            } elseif($this->ReadPropertyBoolean("StatusImage")) {
                imagestring($source, 4, 5, 460, utf8_decode($TextStatus), $col);
            }
            imageline($source, 0, 455, 640, 455, $col);
        }
        imagejpeg($source, $filename, 100);
        ImageDestroy($source);
        IPS_SetMediaFile($media_id, $media_file, true);
        //IPS_SetMediaContent($media_id, base64_encode(file_get_contents($_FILES['image']['tmp_name'])));
        $this->log('UpdateImage - Semaphore leaved' );
        // Mäher ist in Ladestation
        $Status = $this->GetValue ("mowerStatus");
        if (($Status == 0) || ($Status == 1) || ($Status == 4) || ($Status == 16) || ($Status == 17)) {
            $this->SetTimerInterval("ROBONECT_UpdateImageTimer", 0);
        }
        IPS_SemaphoreLeave( $semaphore );
    }

    protected function log( string $text ) {
        if ( $this->ReadPropertyBoolean("DebugLog") ) {
            $this->SendDebug( "Robonect", $text, 0 );
        };
    }

    protected function registerProfiles() {
        // Generate Variable Profiles
        if (!IPS_VariableProfileExists('ROBONECT_Status')) {
            IPS_CreateVariableProfile('ROBONECT_Status', 1);
            IPS_SetVariableProfileIcon('ROBONECT_Status', '');
            IPS_SetVariableProfileAssociation("ROBONECT_Status", 0, "Status wird ermittelt", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Status", 1, "geparkt", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Status", 2, "mäht", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Status", 3, "sucht die Ladestation", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Status", 4, "lädt", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Status", 5, "sucht", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Status", 7, "Fehlerstatus", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Status", 8, "Schleifensignal verloren", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Status", 16, "abgeschaltet", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Status", 17, "schläft", "", 0xFFFFFF);
        }

        if (!IPS_VariableProfileExists('ROBONECT_SubStatus')) {
            IPS_CreateVariableProfile('ROBONECT_SubStatus', 1);
            IPS_SetVariableProfileIcon('ROBONECT_SubStatus', 'Repeat');
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 1, "fährt", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 2, "draussen", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 3, "", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 4, "Räder rutschen", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 5, "", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 6, "Kollision", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 7, "Angehoben", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 8, "Spiralschnitt", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 9, "folgt Leitdraht 1 zur Ladestation", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 10, "folgt rechtem Begrenzungsdraht", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 11, "dockt an", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 12, "Schnellladung", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 13, "", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 14, "Ladung abgeschlossen", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 15, "Ausfahrtswinkel", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 16, "folgt Leitdraht 1 zum Startpunkt", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 17, "", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 18, "Kein Schleifensignal!", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 19, "Mähmotor blockiert!", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 20, "", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 21, "folgt Leitdraht 2 zur Ladestation", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 22, "", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 23, "folgt Leitdraht 2 zum Startpunkt", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 24, "", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_SubStatus", 25, "folgt linkem Begrenzungsdraht", "", 0xFFFFFF);
        }            

        if (!IPS_VariableProfileExists('ROBONECT_InteractiveMode')) {
            IPS_CreateVariableProfile('ROBONECT_InteractiveMode', 1);
            IPS_SetVariableProfileIcon('ROBONECT_InteractiveMode', 'Ok');
            IPS_SetVariableProfileAssociation("ROBONECT_InteractiveMode", 0, "manuell", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_InteractiveMode", 1, "Timer", "Clock", 0xFFFFFF);
        }

        if (!IPS_VariableProfileExists('ROBONECT_TimerTransmitAction')) {
            IPS_CreateVariableProfile('ROBONECT_TimerTransmitAction', 1);
            IPS_SetVariableProfileIcon('ROBONECT_TimerTransmitAction', 'TurnRight');
            IPS_SetVariableProfileAssociation("ROBONECT_TimerTransmitAction", 0, "vom Robonect lesen", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_TimerTransmitAction", 1, "an Robonect übertragen", "", 0xFFFFFF);
        }

        if (!IPS_VariableProfileExists('ROBONECT_Modus')) {
            IPS_CreateVariableProfile('ROBONECT_Modus', 1);
            IPS_SetVariableProfileIcon('ROBONECT_Modus', '');
            IPS_SetVariableProfileAssociation("ROBONECT_Modus", 0, "automatisch", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Modus", 1, "manuell", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Modus", 2, "Zuhause", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Modus", 3, "Demo", "", 0xFFFFFF);
        }

        if (!IPS_VariableProfileExists('ROBONECT_TimerStatus')) {
            IPS_CreateVariableProfile('ROBONECT_TimerStatus', 1);
            IPS_SetVariableProfileIcon('ROBONECT_TimerStatus', '');
            IPS_SetVariableProfileAssociation("ROBONECT_TimerStatus", 0, "deaktiviert", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_TimerStatus", 1, "aktiv", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_TimerStatus", 2, "Standby", "", 0xFFFFFF);
        }

        if ( !IPS_VariableProfileExists('ROBONECT_DoorStatus') ) {
            IPS_CreateVariableProfile('ROBONECT_DoorStatus', 0 );
            IPS_SetVariableProfileIcon('ROBONECT_DoorStatus', '' );
            IPS_SetVariableProfileAssociation("ROBONECT_DoorStatus", true, "Ja", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_DoorStatus", false, "Nein", "", 0xFFFFFF);
        }

        if (!IPS_VariableProfileExists('ROBONECT_ManualAction')) {
            IPS_CreateVariableProfile('ROBONECT_ManualAction', 1);
            IPS_SetVariableProfileIcon('ROBONECT_ManualAction', 'Ok');
            IPS_SetVariableProfileAssociation("ROBONECT_ManualAction", 0, "jetzt mähen", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_ManualAction", 1, "pause", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_ManualAction", 2, "mähen beenden", "", 0xFFFFFF);
        }

        if (!IPS_VariableProfileExists('ROBONECT_MQTTStatus')) {
            IPS_CreateVariableProfile('ROBONECT_MQTTStatus', 1);
            IPS_SetVariableProfileIcon('ROBONECT_MQTTStatus', '');
            IPS_SetVariableProfileAssociation("ROBONECT_MQTTStatus", 0, "offline",  "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_MQTTStatus", 1, "online", "", 0xFFFFFF);
        }

        if ( !IPS_VariableProfileExists('ROBONECT_JaNein') ) {
            IPS_CreateVariableProfile('ROBONECT_JaNein', 0 );
            IPS_SetVariableProfileIcon('ROBONECT_JaNein', '' );
            IPS_SetVariableProfileAssociation("ROBONECT_JaNein", true, "Ja", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_JaNein", false, "Nein", "", 0xFFFFFF);
        }

        if ( !IPS_VariableProfileExists('ROBONECT_Stunden') ) {
            IPS_CreateVariableProfile('ROBONECT_Stunden', 1 );
            IPS_SetVariableProfileDigits('ROBONECT_Stunden', 0 );
            IPS_SetVariableProfileIcon('ROBONECT_Stunden', 'Clock' );
            IPS_SetVariableProfileText('ROBONECT_Stunden', "", " h" );
        }
        
        if ( !IPS_VariableProfileExists('ROBONECT_Tage') ) {
            IPS_CreateVariableProfile('ROBONECT_Tage', 1 );
            IPS_SetVariableProfileDigits('ROBONECT_Tage', 0 );
            IPS_SetVariableProfileIcon('ROBONECT_Tage', 'Clock' );
            IPS_SetVariableProfileText('ROBONECT_Tage', "", " d" );
        }

        if ( !IPS_VariableProfileExists('ROBONECT_Spannung') ) {
            IPS_CreateVariableProfile('ROBONECT_Spannung', 2 );
            IPS_SetVariableProfileDigits('ROBONECT_Spannung', 1 );
            IPS_SetVariableProfileIcon('ROBONECT_Spannung', '' );
            IPS_SetVariableProfileText('ROBONECT_Spannung', "", " V" );
        }
        
        if (!IPS_VariableProfileExists('ROBONECT_MilliAmpereStunde')) {
            IPS_CreateVariableProfile('ROBONECT_MilliAmpereStunde', 1);
            IPS_SetVariableProfileDigits('ROBONECT_MilliAmpereStunde', 1);
            IPS_SetVariableProfileIcon('ROBONECT_MilliAmpereStunde', 'Electricity');
            IPS_SetVariableProfileText('ROBONECT_MilliAmpereStunde', "", " mAh");
        }

        if (!IPS_VariableProfileExists('ROBONECT_Weekdays')) {
            IPS_CreateVariableProfile('ROBONECT_Weekdays', 1);
            IPS_SetVariableProfileIcon('ROBONECT_Weekdays', 'Calendar');
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x01", "Mo", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x02", "Di", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x03", "Mo, Di", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x04", "Mi", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x05", "Mo, Mi", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x06", "Di, Mi", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x07", "Mo, Di, Mi", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x08", "Do", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x09", "Mo, Do", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x0A", "Di, Do", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x0B", "Mo, Di, Do", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x0C", "Mi, Do", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x0D", "Mo, Mi, Do", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x0E", "Di, Mi, Do", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x0F", "Mo, Di, Mi, Do", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x10", "Fr", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x11", "Mo, Fr", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x12", "Di, Fr", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x13", "Mo, Di, Fr", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x14", "Mi, Fr", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x16", "Di, Mi, Fr", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x17", "Mo, Di, Mi, Fr", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x18", "Do, Fr", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x19", "Mo, Do, Fr", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x1A", "Di, Do, Fr", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x1B", "Mo, Di, Do, Fr", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x1C", "Mi, Do, Fr", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x1D", "Mo, Mi, Do, Fr", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x1E", "Di, Mi, Do, Fr", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x1F", "Mo, Di, Mi, Do, Fr", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x20", "Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x21", "Mo, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x22", "Di, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x23", "Mo, Di, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x24", "Mi, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x25", "Mo, Mi, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x26", "Di, Mi, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x27", "Mo, Di, Mi, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x28", "Do, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x29", "Mo, Do, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x2A", "Di, Do, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x2B", "Mo, Di, Do, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x2C", "Mi, Do, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x2D", "Mo, Mi, Do, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x2E", "Di, Mi, Do, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x2F", "Mo, Di, Mi, Do, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x30", "Fr, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x31", "Mo, Fr, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x32", "Di, Fr, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x33", "Mo, Di, Fr, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x34", "Mi, Fr, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x35", "Mo, Mi, Fr, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x36", "Di, Mi, Fr, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x37", "Mo, Di, Mi, Fr, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x38", "Do, Fr, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x39", "Mo, Do, Fr, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x3A", "Di, Do, Fr, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x3B", "Mo, Di, Do, Fr, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x3C", "Mi, Do, Fr, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x3D", "Mo, Mi, Do, Fr, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x3E", "Di, Mi, Do, Fr, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x3F", "Mo, Di, Mi, Do, Fr, Sa", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x40", "So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x41", "Mo, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x42", "Di, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x43", "Mo, Di, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x44", "Mi, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x45", "Mo, Mi, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x46", "Di, Mi, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x47", "Mo Di, Mi, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x48", "Do, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x49", "Mo, Do, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x4A", "Di, Do, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x4B", "Mo, Di, Do, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x4C", "Mi, Do, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x4D", "Mo, Mi, Do, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x4E", "Di, Mi, Do, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x4F", "Mo, Di, Mi, Do, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x50", "Fr, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x51", "Mo, Fr, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x52", "Di, Fr, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x53", "Mo, Di, Fr, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x54", "Mi, Fr, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x55", "Mo, Mi, Fr, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x56", "Di, Mi, Fr, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x57", "Mo Di, Mi, Fr, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x58", "Do, Fr, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x59", "Mo, Do, Fr, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x5A", "Di, Do, Fr, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x5B", "Mo, Di, Do, FR, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x5C", "Mi, Do, Fr, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x5D", "Mo, Mi, Do, Fr, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x5E", "Di, Mi, Do, Fr, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x5F", "Mo, Di, Mi, Do, Fr, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x60", "Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x61", "Mo, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x62", "Di, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x63", "Mo, Di, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x64", "Mi, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x65", "Mo, Mi, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x66", "Di, Mi, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x67", "Mo Di, Mi, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x68", "Do, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x69", "Mo, Do, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x6A", "Di, Do, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x6B", "Mo, Di, Do, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x6C", "Mi, Do, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x6D", "Mo, Mi, Do, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x6E", "Di, Mi, Do, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x6F", "Mo, Di, Mi, Do, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x70", "Fr, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x71", "Mo, Fr, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x72", "Di, Fr, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x73", "Mo, Di, Fr, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x74", "Mi, Fr, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x75", "Mo, Mi, Fr, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x76", "Di, Mi, Fr, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x77", "Mo Di, Mi, Fr, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x78", "Do, Fr, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x79", "Mo, Do, Fr, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x7A", "Di, Do, Fr, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x7B", "Mo, Di, Do, Fr, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x7C", "Mi, Do, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x7D", "Mo, Mi, Do, Fr, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x7E", "Di, Mi, Do, Fr, Sa, So", "", 0xFFFFFF);
            IPS_SetVariableProfileAssociation("ROBONECT_Weekdays", "0x7F", "Mo, Di, Mi, Do, Fr, Sa, So", "", 0xFFFFFF);
        }
    }

    protected function registerVariables() {

        //--- Basic Data ---------------------------------------------------------
        $this->RegisterVariableString( "mowerName", "Name", "", 0);
        $this->RegisterVariableString("mowerSerial", "Seriennummer", "", 1 );

        // Interactive --------------------------------------------------------------

        $this->RegisterVariableInteger("mowerModeInteractive", "Modus", "ROBONECT_InteractiveMode", 20);
        $this->EnableAction("mowerModeInteractive");
        $this->RegisterVariableInteger("manualAction", "Aktion", "ROBONECT_ManualAction", 21 );
        $this->EnableAction("manualAction");

        //--- Status -------------------------------------------------------------
        $this->RegisterVariableBoolean("doorStatus", "Status Garagentor", "ROBONECT_DoorStatus", 30);
        $this->RegisterVariableInteger("mowerMode", "Modus", "ROBONECT_Modus", 31);
        $this->RegisterVariableInteger("mowerStatus", "Status", "ROBONECT_Status", 32);
        $this->RegisterVariableString("mowerStatusPlain", "Status (Klartext)", "", 33);
        $this->RegisterVariableInteger("mowerSubstatus", "Substatus", "ROBONECT_SubStatus", 34);
        $this->RegisterVariableString("mowerSubstatusPlain", "Substatus (Klartext)", "", 35);
        $this->RegisterVariableBoolean("mowerStopped", "man. angehalten", "ROBONECT_JaNein", 36);
        $this->RegisterVariableInteger("mowerStatusSince", "Status seit", "~UnixTimestamp", 37);
        $this->RegisterVariableString("statusSinceDescriptive", "Status seit", "", 38);

        //--- Battery --------------------------------------------------------------
        $this->RegisterVariableInteger("mowerBatterySoc", "Akkustand", "~Battery.100", 40);
        $this->RegisterVariableFloat("mowerVoltageBattery", "Akku-Spannung", "ROBONECT_Spannung", 41);
        $this->RegisterVariableFloat("BatteryCharging", $this->Translate("Battery charging current"), "~Milliampere", 42);
        $this->RegisterVariableFloat("BatteryTemp", $this->Translate("Battery temperatur"), "~Temperature", 43);
        $this->RegisterVariableInteger("BatteryCapacity", $this->Translate("Battery capacity"), "ROBONECT_MilliAmpereStunde", 44);
        $this->RegisterVariableInteger("BatteryRemaining", $this->Translate("Battery remaining"), "ROBONECT_MilliAmpereStunde", 45);
        //--- Conditions --------------------------------------------------------------
        $this->RegisterVariableFloat("mowerVoltageInternal", "Interne Spannung", "ROBONECT_Spannung", 52);
        $this->RegisterVariableFloat("mowerVoltageExternal", "Externe Spannung", "ROBONECT_Spannung", 53);
        $this->RegisterVariableInteger("mowerHours", "Arbeitsstunden", "ROBONECT_Stunden", 54);
        $this->RegisterVariableInteger( "mowerWlanStatus", "WLAN Signalstärke", "~Intensity.100", 55 );
        $this->RegisterVariableInteger( "mowerMqttStatus", "MQTT Status", "ROBONECT_MQTTStatus", 56 );
        $this->RegisterVariableFloat( "mowerTemperature", "Temperatur im Rasenmäher", "~Temperature", 57 );
        $this->RegisterVariableInteger( "mowerHumidity", "Feuchtigkeit im Rasenmäher", "~Humidity", 58 );
        $this->RegisterVariableInteger( "mowerBladesQuality", "Qualität der Messer", "~Intensity.100", 59 );
        $this->RegisterVariableInteger( "mowerBladesOperatingHours", "Betriebsstunden der Messer", "ROBONECT_Stunden", 60 );
        $this->RegisterVariableInteger( "mowerBladesAge", "Alter der Messer", "ROBONECT_Tage", 61 );

        //--- Error List --------------------------------------------------------------
        $this->RegisterVariableInteger( "mowerErrorCount", "Fehlercode", "", 70 );
        $this->RegisterVariableString( "mowerErrorMessage", "Fehlermeldungen", "", 71 );

        //--- Timer --------------------------------------------------------------

        if (!$TimerCat = @IPS_GetCategoryIDByName('Timers', $this->InstanceID)) {
            //PS_GetCategoryIDByName (string $KategorieName, int $ÜbergeordneteID) 
            $TimerCat = IPS_CreateCategory();   // Kategorie anlegen
            IPS_SetName($TimerCat, "Timers");   // Kategorie auf Timer umbenennen
            IPS_SetParent($TimerCat, $this->InstanceID); // Kategorie Timer einsortieren unter der Robonect Instanz
        }
        $Position = 0;
        for ($i = 1; $i <= 14; $i++) {
            if ( $i < 10) {
                $Name = "Timer 0".$i; 
            } else {
                $Name = "Timer ".$i; 
            }
            $Ident = "Timer".$i;
            if (!@IPS_GetObjectIDByIdent($Ident."enable", $TimerCat)) {
                $TimerStatus = $this->RegisterVariableBoolean($Ident."enable", $Name." Status", "ROBONECT_JaNein", 200 + $Position);
                IPS_SetParent($TimerStatus, $TimerCat); // Timer Status unter die Kategory Timer verschieben.
            }
            if (!@IPS_GetObjectIDByIdent($Ident."start", $TimerCat)){
                $TimerStart = $this->RegisterVariableString( $Ident."start", $Name." Start", "", 201 + $Position);
                IPS_SetParent($TimerStart, $TimerCat); // Timer Start unter die Kategory Timer verschieben.
            }
            if (!@IPS_GetObjectIDByIdent($Ident."end", $TimerCat)) {
                $TimerEnd = $this->RegisterVariableString( $Ident."end", $Name.' '.$this->Translate('End'), "", 202 + $Position);
                IPS_SetParent($TimerEnd, $TimerCat); // Timer End unter die Kategory Timer verschieben.
            }
            if (!@IPS_GetObjectIDByIdent($Ident."weekdays", $TimerCat)) {
                $TimerWeekdays = $this->RegisterVariableInteger( $Ident."weekdays", $Name.' '.$this->Translate('Weekdays'), "ROBONECT_Weekdays", 203 + $Position);
                IPS_SetParent($TimerWeekdays, $TimerCat); // Timer Weekdays unter die Kategory Timer verschieben.
            }
            $Position = $Position + 4;
        }
        $this->RegisterVariableInteger( "mowerNextTimerstart", "nächster Timerstart", "~UnixTimestamp", 92 );
        $this->RegisterVariableInteger("timerTransmitAction", "Timer lesen/schreiben", "ROBONECT_TimerTransmitAction", 93 );
        $this->EnableAction("timerTransmitAction");

        //--- Weather --------------------------------------------------------------
        $this->RegisterVariableBoolean("WeatherBreak", "Wetterpause", "ROBONECT_JaNein", 80);
        $this->RegisterVariableInteger("WeatherHumidity", "Luft Feuchtigkeit", "~Humidity", 81);
        $this->RegisterVariableBoolean("WeatherRain", "Es regnet", "ROBONECT_JaNein", 82);
        $this->RegisterVariableFloat("WeatherTemperature", "Aussen Temperatur", "~Temperature", 83);
        $this->RegisterVariableString("WeatherService", "Wetterdienst", "", 84);

        //--- Clock -------------------------------------------------------------
        $this->RegisterVariableInteger( "mowerUnixTimestamp", "Interner Unix Zeitstempel", "~UnixTimestamp", 110 );

        //--- Camera ------------------------------------------------------------
        if ($this->ReadPropertyBoolean( "CameraInstalled" )) {
            $media_file =  'media/' . 'Cam.' . $this->InstanceID . '.jpg';
            if ($this->ReadPropertyBoolean("MediaElements")) {
                // Erstellen Media gesetzt 
                if (!$MediaCat = @IPS_GetCategoryIDByName('Media', $this->InstanceID)) {
                    // Kategorie existiert noch nicht
                    $MediaCat = IPS_CreateCategory();   // Kategorie anlegen
                    IPS_SetName($MediaCat, "Media");   // Kategorie auf Media umbenennen
                    IPS_SetParent($MediaCat, $this->InstanceID); // Kategorie Media einsortieren unter der Robonect Instanz
                }
            } else {
                $MediaCat = $this->InstanceID;
            }
            if (!$media_id = @IPS_GetMediaIDByFile($media_file)) {
                $path = IPS_GetKernelDir();
                if(is_dir($path) == false) mkdir($path);
                $filename = $path . $media_file;
                $im = imagecreatetruecolor(120, 20);
                $text_color = imagecolorallocate($im, 233, 14, 91);
                imagestring($im, 1, 5, 5,  'A new empty cam picture', $text_color);
                imagejpeg($im, $filename);
                // Memory freigeben
                imagedestroy($im);
                $media_id = IPS_CreateMedia(1);
                IPS_SetMediaFile($media_id, $filename, true);
                IPS_SetName($media_id, $this->Translate('Camera picture'));
            }
            // move to instance
            IPS_SetParent($media_id, $MediaCat);
        }
 
        //--- Media
        if ($this->ReadPropertyBoolean("MediaElements")) {
            if (!$MediaCat = @IPS_GetCategoryIDByName('Media', $this->InstanceID)) {
                $MediaCat = IPS_CreateCategory();   // Kategorie anlegen
                IPS_SetName($MediaCat, "Media");   // Kategorie auf Timer umbenennen
                IPS_SetParent($MediaCat, $this->InstanceID); // Kategorie Timer einsortieren unter der Robonect Instanz
            }
            // Batterie Spannung Chart
            $BatteryChartFile ='media/' . 'ChartBattVolt.' . $this->InstanceID . '.chart';
            $this->NewMediaChart($MediaCat, "ChartBatteryVoltage", "Battery voltage chart", $BatteryChartFile, $this->GetIDForIdent("mowerVoltageBattery"), $this->ReadPropertyInteger("ChartBatteryVoltageFill"), $this->ReadPropertyInteger("ChartBatteryVoltageLine"), "ROBONECT_Spannung", "Voltaage");
            // Batterie Kapazität Chart
            $BatteryChartFile ='media/' . 'ChartBattCapacity.' . $this->InstanceID . '.chart';
            $this->NewMediaChart($MediaCat, "ChartBatteryCapacity", "Battery capacity chart", $BatteryChartFile, $this->GetIDForIdent("BatteryRemaining"), $this->ReadPropertyInteger("ChartBatteryCapacityFill"), $this->ReadPropertyInteger("ChartBatteryCapacityLine"), "~Milliampere", "Capacity");
            // Batterie Ladestrom Chart
            $BatteryChartFile ='media/' . 'ChartBattCharging.' . $this->InstanceID . '.chart';
            $this->NewMediaChart($MediaCat, "ChartBatteryCharging", "Battery charging chart", $BatteryChartFile, $this->GetIDForIdent("BatteryCharging"), $this->ReadPropertyInteger("ChartBatteryChargingFill"), $this->ReadPropertyInteger("ChartBatteryChargingLine"), "ROBONECT_MilliAmpereStunde", "Charging");
            // Batterie Temperatur Chart
            $BatteryChartFile ='media/' . 'ChartBattTemperature.' . $this->InstanceID . '.chart';
            $this->NewMediaChart($MediaCat, "ChartBatteryTemperature", "Battery temperature chart", $BatteryChartFile, $this->GetIDForIdent("BatteryTemp"), $this->ReadPropertyInteger("ChartBatteryTemperatureFill"), $this->ReadPropertyInteger("ChartBatteryTemperatureLine"), "~Temperature", "Temperature");
        }

        //----HTMLBox
        if ($this->ReadPropertyBoolean("HtmlBoxElements")) {
            if (!$HTMLboxCat = @IPS_GetCategoryIDByName('HTMLBox', $this->InstanceID)) {
                $HTMLboxCat = IPS_CreateCategory();   // Kategorie anlegen
                    IPS_SetName($HTMLboxCat, "HTMLBox");   // Kategorie auf Timer umbenennen
                    IPS_SetParent($HTMLboxCat, $this->InstanceID); // Kategorie Timer einsortieren unter der Robonect Instanz
            }
            if (!@IPS_GetObjectIDByIdent("Timerlist", $HTMLboxCat)) {
                IPS_SetParent($this->RegisterVariableString("Timerlist", $this->Translate('Timerlist'), "~HTMLBox", 201), $HTMLboxCat); // Timer Weekdays unter die Kategory Timer verschieben.
            }
            if (!@IPS_GetObjectIDByIdent("Errorlist", $HTMLboxCat)) {
                IPS_SetParent($this->RegisterVariableString("Errorlist", $this->Translate('Errorlist'), "~HTMLBox", 202), $HTMLboxCat); // Timer Weekdays unter die Kategory Timer verschieben.
            }
            if (!@IPS_GetObjectIDByIdent("Batterylist", $HTMLboxCat)) {
                IPS_SetParent($this->RegisterVariableString("Batterylist", $this->Translate('Batterylist'), "~HTMLBox", 203), $HTMLboxCat); // Timer Weekdays unter die Kategory Timer verschieben.
            }
            if (!@IPS_GetObjectIDByIdent("TimeStatlist", $HTMLboxCat)) {
                IPS_SetParent($this->RegisterVariableString("TimeStatlist", $this->Translate('Operation hours list'), "~HTMLBox", 204), $HTMLboxCat); // Timer Weekdays unter die Kategory Timer verschieben.
            }
             $this->SetErrorBox();
            $this->GetBatteryData();
            $this->SetTimeStatBox();
        }

    }


    #================================================================================================
    public function ForwardData($JSONString) {
    #================================================================================================
         
            // Empfangene Daten von der Device Instanz
            $this->SendDebug("ForwardData", $JSONString, 0);
            $data = json_decode($JSONString);
            if($this->GetStatus() == 201){
                $this->SendDebug("ForwardData", $this->Translate("Forwarding rejected. Mower is offline"), 0);
                $this->LogMessage('[ID: '.$this->InstanceID.'] '.$this->Translate("Forwarding rejected. Mower is offline")." - ".$data->Payload, KL_ERROR);
            }elseif($this->GetStatus() == 202){
                $this->SendDebug("ForwardData", $this->Translate("Forwarding rejected. MQTT-Landroid-Bridge is offline"), 0);
                $this->LogMessage('[ID: '.$this->InstanceID.'] '.$this->Translate("Forwarding rejected. MQTT-Landroid-Bridge is offline")." - ".$data->Payload, KL_ERROR);
            }else{
                $this->sendMQTT($data->Topic, $data->Payload);
            }
        }
    
    #================================================================================================
    protected function sendMQTT($Topic, $Payload) {
    #================================================================================================
        $retain = false; // Solange der IPS MQTT Server noch kein Retain kann

	    $Data['DataID'] = '{043EA491-0325-4ADD-8FC2-A30C8EEB4D3F}';
	    $Data['PacketType'] = 3;
	    $Data['QualityOfService'] = 0;
	    $Data['Retain'] = boolval($retain);
	    $Data['Topic'] = $this->ReadPropertyString("MQTTTopic").$Topic;
	    $Data['Payload'] = $Payload;

	    $DataJSON = json_encode($Data, JSON_UNESCAPED_SLASHES);

        $this->SendDebug(__FUNCTION__ . 'MQTT Server', $DataJSON, 0);
        $result = @$this->SendDataToParent($DataJSON);

        //MQTT Client
        /* 
       $Buffer['PacketType'] = 3;
        $Buffer['QualityOfService'] = 0;
        $Buffer['Retain'] = boolval($retain);
        $Buffer['Topic'] = $Topic;
        $Buffer['Payload'] = $Payload;
        $BufferJSON = json_encode($Buffer, JSON_UNESCAPED_SLASHES);

        $Client['DataID'] = '{97475B04-67C3-A74D-C970-E9409B0EFA1D}';
        $Client['Buffer'] = $BufferJSON;

        $ClientJSON = json_encode($Client);
        $this->SendDebug(__FUNCTION__ . 'MQTT Client', $ClientJSON, 0);
        $resultClient = @$this->SendDataToParent($ClientJSON);
        */
        if ($result === false) {
            $last_error = error_get_last();
            return $last_error['message'];
        }
        return "Success";
    }

    #================================================================================================
    protected function NewMediaChart (int $ParentID, string $ChartIdent, string $ChartName, string $ChartFile, string $ArchivVarID, int $ChartFill, int $ChartLine, string $ChartProfile, string $Title) {
    #================================================================================================
        if (!$ChartID = @IPS_GetMediaIDByFile($ChartFile)) {
            // Get Archiv ID
            $ArchivID = IPS_GetInstanceListByModuleID("{43192F0B-135B-4CE7-A0A7-1475603F3060}")[0];
            // Archivierung auf mowerVoltageBattery aktiviren
            if (!AC_GetLoggingStatus($ArchivID, $ArchivVarID)) {
                AC_SetLoggingStatus($ArchivID, $ArchivVarID, true);
                AC_SetAggregationType($ArchivID, $ArchivVarID, 0);
                AC_SetGraphStatus($ArchivID, $ArchivVarID, false);
                IPS_ApplyChanges($ArchivID);
            }
    
            $Json = '{"datasets": [{"variableID": '.$ArchivVarID.',"fillColor": "#'.substr("000000".dechex($ChartFill), -6).'","strokeColor": "#'.substr("000000".dechex($ChartLine), -6).'","title": "'.$this->Translate($Title).'","timeOffset": 0}],"profile": "'.$ChartProfile.'","type": "line"}';
    
            $ChartID = IPS_CreateMedia(4);
            IPS_SetParent($ChartID, $ParentID);
            IPS_SetIdent($ChartID, $ChartIdent);
            IPS_SetPosition($ChartID, 2);
            IPS_SetMediaCached($ChartID, false);
            IPS_SetName($ChartID, $this->Translate($ChartName));
            IPS_SetMediaFile($ChartID, $ChartFile, false);
            IPS_SetMediaContent($ChartID, base64_encode($Json));
            IPS_SendMediaEvent($ChartID);
        }
        return $ChartID;
    }
    
    #================================================================================================
    public function SetTimerBox(int $number = null){
    #================================================================================================
        IPS_SemaphoreEnter("TimerBuffer", 3000);
        if ($TimerCatID = @IPS_GetCategoryIDByName('Timers',59802)) {
            $Buffer = array();
            for ($i = 1; $i <= 14; $i++) {
                $Timer = array (
                    "id" => $i,
                    "enabled" => GetValueBoolean(IPS_GetObjectIDByIdent("Timer".$i."enable", $TimerCatID)),
                    "start" => GetValueString(IPS_GetObjectIDByIdent("Timer".$i."start", $TimerCatID)),
                    "end" => GetValueString(IPS_GetObjectIDByIdent("Timer".$i."end",$TimerCatID))
                );
    
                $Weekdays = array_reverse(str_split(base_convert(GetValueInteger(IPS_GetObjectIDByIdent("Timer".$i."weekdays", $TimerCatID)), 10, 2)));
                $Count = 0;
                foreach (["mo","di","mi","do","fr","sa","so"] as $Day) {
                    if ((count($Weekdays) > $Count) && ($Weekdays[$Count])) {
                        $Timer["weekdays"][$Day] = $Weekdays[$Count];
                    } else {
                        $Timer["weekdays"][$Day] = 0;
                    }
                    $Count++;
                }
                $Buffer["Timer".$i] =  $Timer;
            }
        } else {
            $this-log("Timer Buffer konnte nicht erstellt werden.");
            return false;
        }
    
        //Hole TimerListID
        if (!$HTMLboxCat = @IPS_GetCategoryIDByName('HTMLBox', $this->InstanceID)) {
            $this->log ("Keine HTMLBox Kategory vorhanden");
            return false;
        }
        if (!$timerListID = @@IPS_GetObjectIDByIdent("Timerlist", $HTMLboxCat)) {
            $this->log("Kein TimerList Objekt vorhanden");
            return false;
        }
     
        // Hintergrundfarbe Hauptfenster, Header und Fooder umwandeln (hex -> rgb)
        list($br, $bg, $bb) = sscanf("#".substr("000000".dechex($this->ReadPropertyInteger("TimerBackground")),-6), "#%02x%02x%02x");
        // Linienfarbe Grid umwandeln (hex -> rgb)
        list($gr, $gg, $gb) = sscanf("#".substr("000000".dechex ($this->ReadPropertyInteger("TimerGridColor")),-6), "#%02x%02x%02x");
        // Linienfarbe Timerauswahl umwandeln (hex -> rgb)
        list($sr, $sg, $sb) = sscanf("#".substr("000000".dechex ($this->ReadPropertyInteger("TimerSelectColor")),-6), "#%02x%02x%02x");
        $width = $this->ReadPropertyInteger("TimerWidth");
        $fontSize = $this->ReadPropertyInteger("TimerFontSize");
        $bgoca = 0.1;
        $goca = 0.3;
        $baroca = 0.8;
        $toca = 0.2;
        $seloca = 0.5;
        $space = $this->ReadPropertyInteger("TimerFooderSpace");
        $HeaderSize = $this->ReadPropertyInteger("TimerHeaderSize");
        $TimerWidth = $this->ReadPropertyInteger("TimerTimerWidth");
        $TimerHigh = $this->ReadPropertyInteger("TimerTimerHigh");
        $FooderHigh = $this->ReadPropertyInteger("TimerFooderSize");
        
        // Listenoffset berechnen
        $xpos = floor(($width - $this->ReadPropertyInteger("TimerTimerWidth")) / 24);
        // Wochentagnamen definieren
        $dayname = array(
            "mo" => "Mo",
            "tu" => "Di",
            "we" => "Mi",
            "th" => "Do",
            "fr" => "Fr",
            "sa" => "Sa",
            "su" => "So"
        );
    
        // Beginn HTML Header
        $htmlBox = "<style type='text/css'>";
        $htmlBox .= "#bgh {float:left;background:rgba(".$br.",".$bg.",".$bb.",".$bgoca.");width:".($fontSize + $xpos / 2)."px;font-size:".$fontSize."px;margin-left:0px;clear:both;}";
        $htmlBox .= "#bgt {float:left;background:rgba(".$br.",".$bg.",".$bb.",".$bgoca.");width:".($width + $xpos / 2)."px;font-size:".$fontSize."px;margin-left:0px;clear:both;}";
        $htmlBox .= "#bgl {float:left;width:".($width + $xpos / 2)."px;font-size:".$fontSize."px;margin-left:0px;clear:both;}";
        $htmlBox .= "#bgf {float:left;background:rgba(".$br.",".$bg.",".$bb.",".$bgoca.");width:".($width + $xpos / 2)."px;font-size:".$fontSize."px;margin-left:0px;clear:both;}";
        $htmlBox .= "#spacer {width:".$width."px;height:".$space."px;margin:auto;clear:both;}";
        // Header - Zeitleiste
        if($this->ReadPropertyBoolean("TimerHeader")){
            $htmlBox .= "#btime {position:relative;float:left;width:".($width + $xpos / 2)."px;border-bottom:1px dotted;}";
            $htmlBox .= "#ttime {float:left;height:".$HeaderSize."px;width:".($TimerWidth - $xpos / 2)."px;}";
            $htmlBox .= ".time {width:".$xpos."px;height:".$HeaderSize."px;text-align:center;display:table-cell;vertical-align:middle;}";
        }
        // Grid-Overlay
        if($this->ReadPropertyBoolean("TimerGrid")){
            $htmlBox .= "#bhline {position:absolute;float:left;width:".($width + $xpos / 2)."px;}";
            $htmlBox .= "#thline {float:left;width:".$TimerWidth."px;border-right:1px dotted rgba(".$gr.",".$gg.",".$gb.",".$goca.");}";
            $htmlBox .= ".hline {width:".($xpos - 1)."px;border-right:1px dotted rgba(".$gr.",".$gg.",".$gb.",".$goca.");}";
        }
        // Timerspalte
        $htmlBox .= "#timer {position:relative;float:left;width:".$TimerWidth."px;height:".$TimerHigh."px;margin-left:0px;}";
        $htmlBox .= "#tbase {position:absolute;width:".$TimerWidth."px;height:".$TimerHigh."px;}";
        $htmlBox .= "#ttext {position:relative;width:".$TimerWidth."px;height:".$TimerHigh."px;display:table-cell;padding-left:5px;text-align:left;vertical-align:middle;}";
        // Main
        $htmlBox .= "#base {width:".$width."px;height:".$TimerHigh."px;margin-left:0px;}";
        // Pause vor Start
        $htmlBox .= "#bb1 {float:left;height:".$TimerHigh."px;margin:auto;}";
        $htmlBox .= "#b1 {height:".$TimerHigh."px;}";
        // Mähzeit
        $htmlBox .= "#bmow {float:left;height:".$TimerHigh."px;margin:auto;}";
        $htmlBox .= "#mow {height:".$TimerHigh."px;opacity:".$baroca.";text-align:center;display:table-cell;vertical-align:middle;}";
        // Pause nach Ende
        $htmlBox .= sprintf ("#bb3 {float:left;height:%d px;margin:auto;}", $TimerHigh);
        $htmlBox .= "#b3 {height:".$TimerHigh."px;}";
        // Fooder - Timertage
        if($this->ReadPropertyBoolean("TimerFooder")){
            $htmlBox .= "#bfoo {float:left;width:".($width + $xpos / 2 - 3)."px;height:".$FooderHigh."px;margin:auto;border:1px dotted;}";
            $htmlBox .= "#foo {height:".$FooderHigh."px;padding-left:15px;text-align:center;display:table-cell;vertical-align:middle;}";
        }
        $htmlBox .= "</style>";
    
        // Zeitleiste - Header
        if($this->ReadPropertyBoolean("TimerHeader")){
            $htmlBox .= "<div id='bgh'>";
            $htmlBox .= "<div><div id='btime'>";
            $htmlBox .= "<div id='ttime'></div>";
            $htmlBox .= "<div style='float:left;'><div class='time'>0</div></div>";
            for ($i = 1; $i <= 23; $i++) {
                $htmlBox .= "<div style='float:left;'><div class='time'>".$i."</div></div>";
            }
            $htmlBox .= "<div><div class='time' style='width:".($xpos - 2)."px;'>24</div></div>";
            $htmlBox .= "</div></div>";
    
            if($this->ReadPropertyBoolean("TimerGrid")){
                $htmlBox .= "<div><div id='bhline' style='margin-top:".floor($HeaderSize/ 4 * 3)."px;'>";
                $htmlBox .= "<div id='thline'> </div>";
                $htmlBox .= "<div style='float:left;'><div class='hline' style='height:".floor($HeaderSize / 4)."px;'> </div></div>";
                for ($i = 0; $i <= 22; $i++) {
                    $htmlBox .= "<div style='float:left;'><div class='hline' style='height:".floor($HeaderSize / 4)."px;'> </div></div>";
                }
                $htmlBox .= "</div></div>";
            }
        }
        $htmlBox .= "</div>";
    
        // Hauptbereich
        $htmlBox .= "<div id='bgt'>";
    
        // Grid-Overlay
        if($this->ReadPropertyBoolean("TimerFooder")) {
            $height = count($Buffer) * $TimerHigh + count($Buffer) * $space + $space * 2 + 1;
        } else {
            $height = count($Buffer) * $TimerHigh + count($Buffer) * $space + $space + 1;
        }
        if($this->ReadPropertyBoolean("TimerGrid")){
            $htmlBox .= "<div><div id='bhline'>";
            $htmlBox .= sprintf ("<div id='thline' style='height: %d px;'> </div>", $height);
            $htmlBox .= "<div style='float:left;'><div class='hline' style='height:".$height."px;'> </div></div>";
            for ($i = 0; $i <= 22; $i++) {
                $htmlBox .= "<div style='float:left;'><div class='hline' style='height:".$height."px;'> </div></div>";
            }
            $htmlBox .= "</div></div>";
        }
    
        $htmlBox .= "<div id='spacer'></div>";
    
        // Timerarrays abarbeiten
        $count = 1;
        foreach($Buffer as $key => $value){
            $activ = $Buffer[$key]['enabled'];
            $start = explode(":", $Buffer[$key]['start']);
            $end = explode(":", $Buffer[$key]['end']);
    
            // Startminuten umwandeln
            if($start[1] == 0) $sm = 0;
            elseif($start[1] == 15) $sm = 0.25;
            elseif($start[1] == 30) $sm = 0.5;
            elseif($start[1] == 45) $sm = 0.75;
            // Gesamtstartzeit berechnen
            if($start[0] == "00") $ton = $sm;
            else $ton = $start[0] + $sm;
    
            // Endminuten umwandeln
            if($end[1] == 0) $em = 0;
            elseif($end[1] == 15) $em = 0.25;
            elseif($end[1] == 30) $em = 0.5;
            elseif($end[1] == 45) $em = 0.75;
            // Gesamtendzeit berechnen
            if($end[0] == "00") $tof = $em;
            else $tof = $end[0] + $em;
    
            // Berechnung der Prozentewerte
            $b1 = $ton * 100 / 24;
            $mow = $tof * 100 / 24 - $b1;
            $b2 = 100 - ($b1 + $mow);
    
            // umwandeln der Werte zur Anzeige in "px"
            $b1px = floor(($width - $TimerWidth) * $b1 / 100);
            $mowpx = floor(($width - $TimerWidth) * $mow / 100);
            $b2px = floor(($width - $TimerWidth) * $b2 / 100);
    
            // zusammenfügen der einzelnen Timerzeilen
            $htmlBox .= "<div id='timer'>";
            if($activ === 0) $htmlBox .= "<div id='tbase' style='background-color:#".substr("000000".dechex($this->ReadPropertyInteger("Timer".$key)),-6).";opacity:".$toca.";'></div>";
            else $htmlBox .= "<div id='tbase' style='background-color:#".substr("000000".dechex($this->ReadPropertyInteger("Timer".$key)),-6).";'></div>";
            $htmlBox .= "<div id='ttext'>".str_replace(array("t", "r"), array("T", "r "), $key)."</div>";
            $htmlBox .= "</div>";
    
            // ausgewählten Timer markieren
            if($number == $Buffer[$key]['id']){
                $days = $Buffer[$key]['weekdays'];
                $timer = str_replace(array("t", "r"), array("T", "r "), $key);
                $timerstat = $Buffer[$key]['enabled'];
    
                if ($this->ReadPropertyBoolean("TimerSelect")) {
                    $htmlBox .= "<div id='base' style='border: 1px solid rgba(".$sr.",".$sg.",".$sb.",".$seloca.");'>";
                } else {
                    $htmlBox .= "<div id='base'>";
                }
            }
            else $htmlBox .= "<div id='base'>";
    
            $htmlBox .= "<div id='bb1' style='width:".$b1px."px;'><div id='b1' width:".$b1px."px;'></div></div>";
            $htmlBox .= "<div id='bmow' style='width:".$mowpx."px;'><div id='mow' style='background-color:#".substr("000000".dechex($this->ReadPropertyInteger("Timer".$key)),-6).";";
    
            if($this->ReadPropertyBoolean("TimerTimerText")){
                if($mowpx < 80) $htmlBox .= "width:".$mowpx."px;'></div></div>";
                elseif($mowpx < 100) $htmlBox .= "width:".$mowpx."px;'>".$Buffer[$key]['start']." - ".$Buffer[$key]['end']."</div></div>";
                else $htmlBox .= "width:".$mowpx."px;'>".$Buffer[$key]['start']." - ".$Buffer[$key]['end']." Uhr</div></div>";
            }
            else $htmlBox .= "width:".$mowpx."px;'></div></div>";
    
            $htmlBox .= "<div id='bb3' style='width:".$b2px."px;'><div id='b2' style='width:".$b2px."px;'></div></div>";
            $htmlBox .= "</div>";
            $htmlBox .= "<div id='spacer'></div>";
        }
        $htmlBox .= "</div>";
    
        // Timerleiste am Ende erstellen - Fooder
        if($this->ReadPropertyBoolean("TimerFooder")){
            $htmlBox .= "<div id='bgf'>";
            $htmlBox .= "<div id='spacer'></div>";
            $htmlBox .= "<div id='bfoo'>";

            if($number !== null) {
                $xday = count($days) * 60;
                $xtimer = 80;
                $xend = ($width + $xpos / 2) - ($xday + $xtimer);
                $days = $Buffer[$key]['weekdays'];
                $timer = str_replace(array("t", "r"), array("T", "r "), $key);
                $timerstat = $Buffer[$key]['enabled'];
        
                if($timerstat === 1) $htmlBox .= "<div id='foo' style='width:".$xtimer.";'>".$timer.": <span style='color: green;'>aktiv</span></div>";
                else $htmlBox .= "<div id='foo' style='width:".$xtimer.";'>".$timer.": <span style='color: red;'>deaktiviert</span></div>";
    
                foreach($days as $key => $value){
                    if($value === 1) $htmlBox .= "<div id='foo' sytle='width:".$xday.";'>".$dayname[$key].": <span style='color: green;'>aktiv</span></div>";
                    else $htmlBox .= "<div id='foo' sytle='width:".$xday.";'>".$dayname[$key].": <span style='color: red;'>inaktiv</span></div>";
                }
    
                $htmlBox .= "<div id='foo' style='width:".$xend.";'></div>";
            }
            else $htmlBox .= "<div id='foo' style='width:100%;'>kein Timer ausgewählt</div>";
    
            $htmlBox .= "</div>";
        }
        $htmlBox .= "</div>";
    
        SetValueString($timerListID, $htmlBox);
        IPS_SemaphoreLeave("TimerBuffer");
        return $htmlBox;
    }

    #================================================================================================
    protected function SetErrorBox() {
    #================================================================================================
         //Hole Errorlist Id
         if (!$HTMLboxCat = @IPS_GetCategoryIDByName('HTMLBox', $this->InstanceID)) {
            $this->log ("Keine HTMLBox Kategory vorhanden");
            return false;
        }
        if (!$ErrorListID = @IPS_GetObjectIDByIdent("Errorlist", $HTMLboxCat)) {
            $this->log("Kein ErrorList Objekt vorhanden");
            return false;
        }
        $data = $this->executeHTTPCommand("error");
        if ((!isset( $data )) || (!$data['successful'])) {
            $this->log("Fehlermeldungen: ".$data);
            return false;
        }
        // Hintergrundfarbe umwandeln (hex -> rgb)
        if ($this->ReadPropertyBoolean("ErrorBackground")) {
            list($er, $eg, $eb) = sscanf("#".substr("000000".dechex($this->ReadPropertyInteger("ErrorBackgroundColor")), -6), "#%02x%02x%02x");
        }
        $oca_tbg = 0.1;
        $timestamp = true;

        $htmlBox = "<style type='text/css'>";
        if (!$this->ReadPropertyBoolean("ErrorBackground")) {
            $bgt = "none";
        } else {
            $bgt = "rgba(".$er.",".$eg.",".$eb.",".$oca_tbg.")";
        }
        $htmlBox .= "#etable {position:relative;float:left;background:".$bgt.";font-size:".$this->ReadPropertyInteger("ErrorFontSize")."px;padding:10px;}";
        $htmlBox .= "#espacer {width:".($this->ReadPropertyInteger("ErrorDateWidth") + $this->ReadPropertyInteger("ErrorHourWidth") + $this->ReadPropertyInteger("ErrorWMessageidth"))."px;height:5px;margin:auto;clear:both;}";
        $htmlBox .= "#edate {float:left;width:".$this->ReadPropertyInteger("ErrorDateWidth")."px;height:".$this->ReadPropertyInteger("ErrorRowHigh")."px;padding:1px;}";
        $htmlBox .= "#etime {float:left;width:".$this->ReadPropertyInteger("ErrorHourWidth")."px;height:".$this->ReadPropertyInteger("ErrorRowHigh")."px;padding:1px;}";
        $htmlBox .= "#etext {float:left;width:".$this->ReadPropertyInteger("ErrorWMessageidth")."px;height:".$this->ReadPropertyInteger("ErrorRowHigh")."px;padding:1px;}";
        $htmlBox .= "</style>";

        if(array_key_exists("errors", $data) and array_key_exists("0", $data['errors']) === true) {
            $htmlBox ="<div id='etable'>";
            $htmlBox .= "<div id='espacer'></div>";
            foreach($data['errors'] as $key => $value) {
                $htmlBox .= "<div>";
                $htmlBox .= "<div id='edate'>".$value['date']."</div>";
                $htmlBox .= "<div id='etime'>".$value['time']."</div>";
                $htmlBox .= "<div id='etime'>Error Code: ".$value['error_code']."</div>";
                $htmlBox .= "<div id='etext'> ".$value['error_message']."</div>";
                $htmlBox .= "</div>";
                $htmlBox .= "<div id='espacer'></div>";
            }
            if ($timestamp) {
                $htmlBox .= "<div style='font-size:10px;text-align:right;'>Update: ".date("d.m.Y H:i:s")."</div>";
            }
            $htmlBox .= "</div>";
        } else {
            $htmlBox .= "<div id='etable'>";
            $htmlBox .= "<div style='font-size:".($conf_error['fsize'] + 2)."px;'>Fehlerspeicher ist leer</div>";
            if($timestamp) {
                $htmlBox .= "<div id='espacer'></div>";
                $htmlBox .= "<div id='espacer'></div>";
                $htmlBox .= "<div style='font-size:10px;text-align:right;'>Update: ".date("d.m.Y H:i:s")."</div>";
            }
            $htmlBox .= "</div>";
        }
        SetValueString($ErrorListID, $htmlBox);
        return $htmlBox;
    }

    #================================================================================================
    protected function SetBatteryBox() {
    #================================================================================================
        // Hole Batterylist ID
        $this->log("Erstelle Batteryliste.");
        if (!$HTMLboxCat = @IPS_GetCategoryIDByName('HTMLBox', $this->InstanceID)) {
            $this->log ("Keine HTMLBox Kategory vorhanden");
            return false;
        }
        if (!$BatteryListID = @IPS_GetObjectIDByIdent("Batterylist", $HTMLboxCat)) {
            $this->log("Kein ErrorList Objekt vorhanden");
            return false;
        }
        $timestamp = true;
        $oca_tbg = 0.1;
        $fsize = $this->ReadPropertyInteger("BatteryFontSize");
        $lwidth = $this->ReadPropertyInteger("BatteryColumWidthNames");
        $vwidth = $this->ReadPropertyInteger("BatteryColumWidthValues");
        $bwidth = $this->ReadPropertyInteger("BatteryBarLength");
        $bheight = $this->ReadPropertyInteger("BatteryBarHigh");
        $col_bg = "#".substr("000000".dechex($this->ReadPropertyInteger("BatteryBarBackground")), -6);
        $col_plus = "#".substr("000000".dechex($this->ReadPropertyInteger("BatteryBarPositivColor")), -6);
        $col_minus = "#".substr("000000".dechex($this->ReadPropertyInteger("BatteryBarNegativColor")), -6);
        // ToDo Facktor Werte ins Formular
        $config	= array(
            "mowerBatterySoc" 		=> array("name" => "Status", "unit" => "%", "factor" => 100),
            "mowerVoltageBattery" 	=> array("name" => "Spannung", "unit" => "V", "factor" => 30),
            "BatteryCapacity" 	    => array("name" => "Kapazität", "unit" => "mAh", "factor" => 1600),
            "BatteryCharging"  	    => array("name" => "Ladestrom", "unit" => "mA", "factor" => 1500),
            "BatteryTemp" 		    => array("name" => "Temperatur", "unit" => "°C", "factor" => 50)
        );
        $BatteryData = array (
            "mowerBatterySoc"       => $this->GetValue("mowerBatterySoc"),
            "mowerVoltageBattery"   => $this->GetValue("mowerVoltageBattery"),
            "BatteryTemp"           => $this->GetValue("BatteryTemp"),
            "BatteryCapacity"       => $this->GetValue("BatteryCapacity"),
        //    "BatteryRemaining"      => $this->GetValue("BatteryRemaining"),
            "BatteryCharging"       => $this->GetValue("BatteryCharging")
        );
        $this->log("Konfigurationsdaten Batteryliste geladen.");

        // Hintergrundfarbe umwandeln (hex -> rgb)
        if ($this->ReadPropertyBoolean("BatteryBackground")) {
            list($tr, $tg, $tb) = sscanf("#".substr("000000".dechex($this->ReadPropertyInteger("BatteryBackgroundColor")), -6), "#%02x%02x%02x");
        }
        $htmlBox = "<style type='text/css'>";
        if(!$this->ReadPropertyBoolean("BatteryBackground")) $bgt = "none";
        else $bgt = "rgba(".$tr.",".$tg.",".$tb.",".$oca_tbg.")";
        $htmlBox .= "#btable {position:relative;float:left;background:".$bgt.";font-size:".$fsize."px;padding:10px;}";
        $htmlBox .= "#bspacer {width:".($bwidth + $lwidth + $vwidth + 6)."px;height:5px;margin:auto;clear:both;}";
        $htmlBox .= "#bcaption {width:".($bwidth + $lwidth + $vwidth + 6)."px;height:15px;font-size:".($fsize + 2)."px;border-bottom:1px solid #FFFFFF;padding: 1px;}";
        $htmlBox .= "#blabel {float:left;background:none;width:".$lwidth."px;height:".$bheight."px;padding:1px;}";
        $htmlBox .= "#bvalue {float:left;background:none;width:".$vwidth."px;height:".$bheight."px;padding:1px;}";
        $htmlBox .= "#bbase {float:left;width:".$bwidth."px;height:".($bheight + 2)."px;}";
        $htmlBox .= "#bbg {background-color:".$col_bg.";width:".$bwidth."px;height:".($bheight - 6)."px;border:1px solid ".$col_bg.";}";
        $htmlBox .= "#bbar {height:".($bheight - 6)."px;}";
        $htmlBox .= "</style>";
    
        $htmlBox .= "<div id='btable'>";
        $htmlBox .= "<div id='bspacer'></div>";
        foreach($BatteryData as $key => $value){
            $htmlBox .= "<div>";
            $htmlBox .= "<div id='blabel'>".$config[$key]['name']."</div>";
            $htmlBox .= "<div id='bvalue'>".$value." ".$config[$key]['unit']."</div>";
            $htmlBox .= "<div id='bbase'>";
            $htmlBox .= "<div id='bbg'>";
            if($value < 0) $htmlBox .= "<div id='bbar' style='float:right;background-color:".$col_minus."; width:".(floor((abs($value) * 100) / $config[$key]['factor']))."%;'></div></div>";
            if($value >= 0)$htmlBox .= "<div id='bbar' style='background-color:".$col_plus.";width:".(floor((abs($value) * 100) / $config[$key]['factor']))."%;'></div></div>";
            $htmlBox .= "</div></div>";
            $htmlBox .= "<div id='bspacer'></div>";
        }
        if($timestamp){
            //$htmlBox .= "<div id='bspacer'></div>";
            $htmlBox .= "<div style='font-size:10px;text-align:right;'>Update: ".date("d.m.Y H:i:s")."</div>";
        }
        $htmlBox .= "</div>";
        SetValueString($BatteryListID, $htmlBox);
        return $htmlBox;
    }

    #================================================================================================
    protected function SetTimeStatBox() {
    #================================================================================================
    Global $conf_time, $timestamp;
         //Hole Errorlist Id
         if (!$HTMLboxCat = @IPS_GetCategoryIDByName('HTMLBox', $this->InstanceID)) {
            $this->log ("Keine HTMLBox Kategory vorhanden");
            return false;
        }
        if (!$TimeStatListID = @IPS_GetObjectIDByIdent("TimeStatlist", $HTMLboxCat)) {
            $this->log("Kein Time Statistik List Objekt vorhanden");
            return false;
        }
        $data = $this->executeHTTPCommand("hour");
        if ((!isset( $data )) || (!$data['successful'])) {
            $this->log("Fehlermeldungen: ".$data);
            return false;
        }
        $height = $this->ReadPropertyInteger("OperHoursHeigh");
        $width = $this->ReadPropertyInteger("OperHoursWidth");
        $h_offset = $this->ReadPropertyInteger("OperHoursHOffset");
        $col_bar = $this->ReadPropertyInteger("OperHoursBarColor");
        $col_txt = $this->ReadPropertyInteger("OperHoursFontColor");
        $oca_bg = 0.1;
        $oca_bgb = 0.3;

        foreach ($data['seek'] as $duration) {
            $timearray['search'][] = $duration['duration'];
        }
        foreach ($data['mowing'] as $duration) {
            $timearray['mow'][] = $duration['duration'];
        }
    
        //Ausgabe der Daten
        // CSS - Teil
        // Hintergrundfarbe umwandeln (hex -> rgb)
        list($tr, $tg, $tb) = sscanf("#".substr("000000".dechex($this->ReadPropertyInteger("OperHoursBackgroundColor")), -6), "#%02x%02x%02x");
        
        // Hintergrundfarbe Bars umwandeln (hex -> rgb)
        list($br, $bg, $bb) = sscanf("#".substr("000000".dechex($this->ReadPropertyInteger("OperHoursFieldBackgroundColor")), -6), "#%02x%02x%02x");
    
        $htmlBox = "<style type='text/css'>";
        $bgt = "rgba(".$tr.",".$tg.",".$tb.",".$oca_bg.")";
        $htmlBox .= "#table {position:relative;float:left;background:".$bgt.";width:".($width + 7)."px;padding: 5px;}";
        $bgl = "rgba(".$br.",".$bg.",".$bb.",".$oca_bgb.")";
        $htmlBox .= "#list {position:relative;background:".$bgl.";width:".$width."px;padding: 0px;}";
        $htmlBox .= "#q-graph {position:relative;width:".$width."px;height:".$height."px;";
        $htmlBox .= "margin: 1.1em 0 3.5em; padding: 0;";
        $htmlBox .= "background:rgba(".$br.",".$bg.",".$bb.",".$oca_bgb.");";
        $htmlBox .= "border-bottom: 1px solid gray; list-style: none; ";
        $htmlBox .= "font: 9px Helvetica, Geneva, sans-serif;}";
        $htmlBox .= "#q-graph ul {margin: 0; padding: 0; list-style: none;}";
        $htmlBox .= "#q-graph li {position: absolute; bottom: 0; width: 150px; z-index: 2; margin: 0; padding: 0; text-align: center; list-style: none;}";
        $htmlBox .= "#q-graph li.qtr {height: ".($height - $h_offset)."px; padding-top: 2px; border-right: none;}";
        $htmlBox .= "#q-graph li.bar {width: 25px; border: 1px solid; border-bottom: none; color: ".$col_txt.";}";
        $htmlBox .= "#q-graph li.bar p {margin: 5px 0 0; padding: 0;}";
        $htmlBox .= "#q-graph li.time {left: 7px; background: ".$col_bar."; border-color: #EDC #BA9 #000 #EDC;}";
        $htmlBox .= "#q-graph #q1 {left: 0;}";
        $htmlBox .= "#q-graph #q2 {left: 32px;}";
        $htmlBox .= "#q-graph #q3 {left: 64px;}";
        $htmlBox .= "#q-graph #q4 {left: 96px;}";
        $htmlBox .= "#q-graph #q5 {left: 128px;}";
        $htmlBox .= "#q-graph #q6 {left: 160px;}";
        $htmlBox .= "#q-graph #q7 {left: 192px;}";
        $htmlBox .= "#q-graph #q8 {left: 224px;}";
        $htmlBox .= "#q-graph #q9 {left: 256px;}";
        $htmlBox .= "#q-graph #q10 {left: 288px;}";
        $htmlBox .= "#q-graph #q11 {left: 320px;}";
        $htmlBox .= "#q-graph #q12 {left: 352px;}";
        $htmlBox .= "#q-graph #q13 {left: 384px;}";
        $htmlBox .= "#q-graph #q14 {left: 416px;}";
        $htmlBox .= "#q-graph #q15 {left: 448px;}";
        $htmlBox .= "#q-graph #q16 {left: 480px;}";
        $htmlBox .= "#q-graph #q17 {left: 512px;}";
        $htmlBox .= "#q-graph #q18 {left: 544px;}";
        $htmlBox .= "#q-graph #q19 {left: 576px;}";
        $htmlBox .= "#q-graph #q20 {left: 608px; border-right: none;}";
        $htmlBox .= "</style>";
    
        // Tabellenerstellung
        $htmlBox .="<body>";
        $htmlBox .= "<div id='table'>";
        $htmlBox .= "<table style='font-size:12px;text-align:left;'>";
        $htmlBox .= "<tr style='height:5px;'><td></td></tr>";
        $htmlBox .= "<tr><th style='font-size:13px;'>Allgemein</th></tr>";
        $htmlBox .= "<tr><td style='border-top: 1px solid #ffffff;'></td></tr>";
        $htmlBox .= '<tr><td>';
        $htmlBox .= "<div id='list'><table>";
        $htmlBox .= "<tr><td width=70>Laufzeit:</td><td>".$data['general']['run']." h</td>";
        $htmlBox .= "<td width=30></td>";
        $htmlBox .= "<td width=70>Suchzeit:</td><td>".$data['general']['search']." h</td>";
        $htmlBox .= "<td width=30></td>";
        $htmlBox .= "<td width=100>Vollladungen:</td><td>".$data['general']['charges']."</td></tr>";
        $htmlBox .= "<tr><td>Mähzeit:</td><td>".$data['general']['mow']." h</td>";
        $htmlBox .= "<td></td>";
        $htmlBox .= "<td>Ladezeit:</td><td>".$data['general']['charge']." h</td>";
        $htmlBox .= "<td></td>";
        $htmlBox .= "<td>Fehler:</td><td>".$data['general']['errors']."</td></tr>";
        $htmlBox .= "</table></div></td></tr>";
        $htmlBox .= "<tr style='height:20px;'><td></td></tr>";
        // Suchzeiten
        $htmlBox .= "<tr><th style='font-size:13px;'>Suchzeiten</th></tr>";
        $htmlBox .= "<tr><td style='border-top: 1px solid #ffffff;'></td></tr>";
        $htmlBox .= "<tr><td>";
        // Bargraph Suchzeiten erzeugen
        $htmlBox .= '<ul id="q-graph">';
        $htmlBox .= "<span><table>";
        $htmlBox .= "<tr><td width=60>Minimale Zeit:</td><td>".min($timearray['search'])." min</td></tr>";
        $htmlBox .= "<tr><td>Maximale Zeit:</td><td>".max($timearray['search'])." min</td></tr>";
        $htmlBox .= "<tr><td>Durchschnitt:</td><td>".floor((array_sum($timearray['search']) / count($timearray['search'])))." min</td></tr>";
        $htmlBox .= "</table></span>";
    
        for($i = 1; $i <= count($timearray['search']); $i++){
            $htmlBox .= '<li class="qtr" id="q'.$i.'">';
            $htmlBox .= '<ul>';
    
            $high = ((($height - $h_offset) * $timearray['search'][$i-1]) / max($timearray['search']));
            if ($high > 0) {
                $htmlBox .= sprintf('<li class="time bar" style="height: %dpx;"><p>%d min</p></li>', $high, $timearray['search'][$i-1]);
            } else {
                $htmlBox .= sprintf('<li class="time bar" style="height: %dpx;"><p style="margin:-15px 0 0;"> min</p></li>', $high, $timearray['search'][$i-1]);
            }    
            $htmlBox .= '</ul>';
            $htmlBox .= '</li>';
        }
        $htmlBox .= '</ul>';
        $htmlBox .= "</td></tr>";
        // Mähzeiten
        $htmlBox .= "<tr><th style='font-size:13px;'>Mähzeiten</th></tr>";
        $htmlBox .= "<tr><td style='border-top: 1px solid #ffffff;'></td></tr>";
        $htmlBox .= "<tr><td>";
        // Bargraph Mähzeiten erzeugen
        $htmlBox .= '<ul id="q-graph">';
        $htmlBox .= "<span><table>";
        $htmlBox .= "<tr><td width=60>Minimale Zeit:</td><td>".min($timearray['mow'])." min</td></tr>";
        $htmlBox .= "<tr><td>Maximale Zeit:</td><td>".max($timearray['mow'])." min</td></tr>";
        $htmlBox .= "<tr><td>Durchschnitt:</td><td>".floor((array_sum($timearray['mow']) / count($timearray['mow'])))." min</td></tr>";
        $htmlBox .= "</table></span>";
   
        for($i = 1; $i <= count($timearray['mow']); $i++){
            $htmlBox .= '<li class="qtr" id="q'.$i.'">';
            $htmlBox .= '<ul>';
    
            $high = ((($height - $h_offset) * $timearray['mow'][$i-1]) / max($timearray['mow']));
            if ($high > 0) {
                $htmlBox .= sprintf('<li class="time bar" style="height: %dpx;"><p> %d min</p></li>', $high, $timearray['mow'][$i-1]);
            } else {
                $htmlBox .= sprintf('<li class="time bar" style="height: %dpx;"><p style="margin:-15px 0 0;">%d min</p></li>', $high, $timearray['mow'][$i-1]);
            }
            $htmlBox .= '</ul>';
            $htmlBox .= '</li>';
        }
        $htmlBox .= '</ul>';
        $htmlBox .= "</td></tr>";
        if($timestamp) $htmlBox .= "<tr style='font-size:10px;text-align:right;'><td>Update: ".date("d.m.Y H:i:s")."</td></tr>";
        $htmlBox .= "</table>";
        $htmlBox .= "</div>";
        $htmlBox .= "<div style='clear:both;'></div>";
        $htmlBox .="</body>";
    
        //    IPS_SemaphoreLeave("openBufferTimeStat");
        SetValueString($TimeStatListID, $htmlBox);
        return $htmlBox;
    }

}
