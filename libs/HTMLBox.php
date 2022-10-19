<?php


#================================================================================================
public function SetTimerBox($number = false){
#================================================================================================
    
    
///    Global $parents, $conf_timer;
    // Daten aus dem Speicher holen
    IPS_SemaphoreEnter("TimerBuffer", 3000);
   
/*    $buffer = robo_GetSetBuffer("read");

    if(array_key_exists("timer", $buffer) === false){
        IPS_SemaphoreLeave("openBuffer");
        robo_SetVariable("roboBoxTimer", "roboBoxID", "noch eine Daten vorhanden, bitte Timer einlesen");
        return;
    }
    elseif(is_array($buffer['timer']) === false){
        IPS_SemaphoreLeave("openBuffer");
        robo_SetVariable("roboBoxTimer", "roboBoxID", "noch eine Daten vorhanden, bitte Timer einlesen");
        return;
    }
    elseif(empty($buffer['timer']) === true){
        IPS_SemaphoreLeave("openBuffer");
        robo_SetVariable("roboBoxTimer", "roboBoxID", "noch eine Daten vorhanden, bitte Timer einlesen");
        return;
    }

    "timer": {
        "timer1": {
            "id":"1",
            "enabled":0,
            "start":"11:00",
            "end":"12:00",
            "weekdays":{
                "mo":1,
                "tu":1,
                "we":1,
                "th":1,
                "fr":1,
                "sa":1,
                "su":1
            }
        },
        "timer2": {
            "id":"2",
            "enabled":1,
            "start":"15:00",
            "end":"22:00",
            "weekdays":{
                "mo":1,
                "tu":1,
                "we":1,
                "th":1,
                "fr":1,
                "sa":1,
                "su":1
            }
        }
    }
    */
    if ($TimerCatID = @IPS_GetCategoryIDByName('Timers',59802)) {
        $Buffer = array();
        for ($i = 1; $i <= 14; $i++) {
            $Timer = array (
                "id" => $i-1,
                "enabled" => GetValueBoolean(IPS_GetObjectIDByIdent("Timer".$i."enable", $TimerCatID)),
                "start" => GetValueString(IPS_GetObjectIDByIdent("Timer".$i."start", $TimerCatID)),
                "end" => GetValueString(IPS_GetObjectIDByIdent("Timer".$i."end",$TimerCatID))
            );

            $Weekdays = array_reverse(str_split(base_convert(GetValueInteger(IPS_GetObjectIDByIdent("Timer".$i."weekdays", $TimerCatID)), 10, 2)));
            //if (!$Weekdays) {
            //    $Weekdays = array (0,0,0,0,0,0,0);
            //}
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
    list($br, $bg, $bb) = sscanf(dechex ($this->ReadPropertyInteger("TimerBackground")), "#%02x%02x%02x");
    // Linienfarbe Grid umwandeln (hex -> rgb)
    list($gr, $gg, $gb) = sscanf(dechex ($this->ReadPropertyInteger("TimerGridColor")), "#%02x%02x%02x");
    // Linienfarbe Timerauswahl umwandeln (hex -> rgb)
    list($sr, $sg, $sb) = sscanf(dechex ($this->ReadPropertyInteger("TimerSelectColor")), "#%02x%02x%02x");
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
        $activ = $buffer[$key]['enabled'];
        $start = explode(":", $buffer[$key]['start']);
        $end = explode(":", $buffer[$key]['end']);

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
        if($activ === 0) $htmlBox .= "<div id='tbase' style='background-color:".$this->ReadPropertyInteger("Timer".$key).";opacity:".$toca.";'></div>";
        else $htmlBox .= "<div id='tbase' style='background-color:".$this->ReadPropertyInteger("Timer".$key).";'></div>";
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
        $htmlBox .= "<div id='bmow' style='width:".$mowpx."px;'><div id='mow' style='background-color:".$this->ReadPropertyInteger("Timer".$key).";";

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

        if($number !== false) {
            $xday = count($days) * 60;
            $xtimer = 80;
            $xend = ($width + $xpos / 2) - ($xday + $xtimer);

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