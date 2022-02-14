<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/_traits.php';  // Generell funktions

// CLASS WasteModule
class WasteModule extends IPSModule {
    //use CalendarHelper;
    use ProfileHelper;
    use EventHelper;
    use DebugHelper;
    use WebhookHelper;

    /**
     * Create.
     */
    public function Create() {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyString('ListGrey', '[]');
        $this->RegisterPropertyString('ListGreen', '[]');
        $this->RegisterPropertyString('ListBrown', '[]');
        $this->RegisterPropertyString('ListYellow', '[]');
        $this->RegisterPropertyString('ListGlas', '[]');

        $this->RegisterPropertyBoolean("cbUpdGrey", true);
        $this->RegisterPropertyBoolean("cbUpdGreen", true);
        $this->RegisterPropertyBoolean("cbUpdBrown", true);
        $this->RegisterPropertyBoolean("cbUpdYellow", true);
        $this->RegisterPropertyBoolean("cbUpdGlas", true);

        // Register daily update timer
        $this->RegisterTimer('UpdateTimer', 0, 'WASTE_Update(' . $this->InstanceID . ');');
    }

    /**
     * Destroy.
     */
    public function Destroy() {
        if (!IPS_InstanceExists($this->InstanceID)) {
            $this->UnregisterHook('/hook/waste' . $this->InstanceID);
        }
        parent::Destroy();
    }

    /**
     * Configuration Form.
     *
     * @return JSON configuration string.
     */
    public function GetConfigurationForm() {
        // read setup
      //  $publicCountry = $this->ReadPropertyString('PublicCountry');
        //  $publicHoliday = $this->ReadPropertyString('PublicRegion');
        // School Vacation
        //  $schoolCountry = $this->ReadPropertyString('SchoolCountry');
        //  $schoolRegion = $this->ReadPropertyString('SchoolRegion');
        //  $schoolName = $this->ReadPropertyString('SchoolName');
        // Debug output
        //  $this->SendDebug('GetConfigurationForm', 'public country=' . $publicCountry . ', public holiday=' . $publicHoliday .
                        //  ', school country=' . $schoolCountry . ', school vacation=' . $schoolRegion . ', school name=' . $schoolName, 0);
        // Get Data
        //  $data = json_decode(file_get_contents(__DIR__ . '/data.json'), true);
        // Get Form
        //  $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        // Holiday Regions
        //  $form['elements'][2]['items'][1]['options'] = $this->GetRegions($data[$publicCountry]);
        // Vacation Regions
        //  $form['elements'][3]['items'][1]['items'][0]['options'] = $this->GetRegions($data[$schoolCountry]);
        // Schools
        //  $form['elements'][3]['items'][1]['items'][1]['options'] = $this->GetSchool($data[$schoolCountry], $schoolRegion);
        // Debug output
        $this->SendDebug(__FUNCTION__, '');
        //  return json_encode($form);
    }

    /**
     * Apply Configuration Changes.
     */
    public function ApplyChanges() {
        // Never delete this line!
        parent::ApplyChanges();

        $this->SendDebug(__FUNCTION__, '');


        $UpdGrey = $this->ReadPropertyBoolean("cbUpdGrey");
        //$this->MaintainVariable('GreyTray', $this->Translate('GreyTray'), vtString, '', 204, $UpdGrey);
        $this->MaintainVariable('GreyCan', 'GreyCan', vtString, '', 201, $UpdGrey);
         
        $UpdGreen = $this->ReadPropertyBoolean("cbUpdGreen");
        //$this->MaintainVariable('GreyTray', $this->Translate('GreyTray'), vtString, '', 204, $UpdGrey);
        $this->MaintainVariable('GreenCan', 'GreenCan', vtString, '', 202, $UpdGreen);

        $UpdBrown = $this->ReadPropertyBoolean("cbUpdBrown");
        //$this->MaintainVariable('GreyTray', $this->Translate('GreyTray'), vtString, '', 204, $UpdBrown);
        $this->MaintainVariable('BrownCan', 'BrownCan', vtString, '', 203, $UpdBrown);

        $UpdYellow = $this->ReadPropertyBoolean("cbUpdYellow");
        //$this->MaintainVariable('GreyTray', $this->Translate('GreyTray'), vtString, '', 204, $UpdGrey);
        $this->MaintainVariable('YellowCan', 'YellowCan', vtString, '', 204, $UpdYellow);

        $UpdGlas = $this->ReadPropertyBoolean("cbUpdGlas");
        //$this->MaintainVariable('GreyTray', $this->Translate('GreyTray'), vtString, '', 204, $UpdGrey);
        $this->MaintainVariable('GlasContainer', 'GlasContainer', vtString, '', 205, $UpdGlas);


        // Webhook for exports
        $this->RegisterHook('/hook/waste' . $this->InstanceID);

        // Calculate next date info update interval
        $this->UpdateTimerInterval('UpdateTimer', 0, 0, 30);
    }

    /**
     * RequestAction.
     *
     *  @param string $ident Ident.
     *  @param string $value Value.
     */
    public function RequestAction($ident, $value) {
        // Debug output
        $this->SendDebug(__FUNCTION__, $ident . ' => ' . $value);
        // Ident == OnXxxxxYyyyy
        switch ($ident) {
            case 'OnImportData':
                $this->OnImportData($value);
                break;
            case 'OnDeleteDays':
                $this->OnDeleteDays($value);
                break;
        }
    }


    public function WASTE_Update() {
        $this->SendDebug(__FUNCTION__, '');
    }
    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * WASTE_Update($id);
     */
    public function Update() {

        $this->SendDebug(__FUNCTION__, '');
   
        if ($this->ReadPropertyBoolean("cbUpdGrey")) {
            $Grey = $this->LookupDays($ts, 'ListGrey');

            try {
                $this->UpdateDay('GreyCan', $Grey);
            } catch (Exception $ex) {
                $this->LogMessage($ex->getMessage(), KL_ERROR);
                $this->SendDebug(__FUNCTION__, 'ERROR GREY CAN: ' . $ex->getMessage(), 0);
            }
        }

        if ($this->ReadPropertyBoolean("cbUpdGreen")) {
            $Green = $this->LookupDays($ts, 'ListGreen');
            
            try {
                $this->UpdateDay('GreenCan', $Green);
            } catch (Exception $ex) {
                $this->LogMessage($ex->getMessage(), KL_ERROR);
                $this->SendDebug(__FUNCTION__, 'ERROR GREEN CAN: ' . $ex->getMessage(), 0);
            }
        }

        if ($this->ReadPropertyBoolean("cbUpdBrown")) {
            $Brown = $this->LookupDays($ts, 'ListBrown');
            
            try {
                $this->UpdateDay('BrownCan', $Brown);
            } catch (Exception $ex) {
                $this->LogMessage($ex->getMessage(), KL_ERROR);
                $this->SendDebug(__FUNCTION__, 'ERROR BROWN CAN: ' . $ex->getMessage(), 0);
            }
        }

        if ($this->ReadPropertyBoolean("cbUpdYellow")) {
            $Yellow = $this->LookupDays($ts, 'ListYellow');
            
            try {
                $this->UpdateDay('YellowCan', $Yellow);
            } catch (Exception $ex) {
                $this->LogMessage($ex->getMessage(), KL_ERROR);
                $this->SendDebug(__FUNCTION__, 'ERROR YELLOW CAN: ' . $ex->getMessage(), 0);
            }
        }

        if ($this->ReadPropertyBoolean("cbUpdGlas")) {
            $Glas = $this->LookupDays($ts, 'ListGlas');
            
            try {
                $this->UpdateDay('GlasContainer', $Glas);
            } catch (Exception $ex) {
                $this->LogMessage($ex->getMessage(), KL_ERROR);
                $this->SendDebug(__FUNCTION__, 'ERROR GLAS CAN: ' . $ex->getMessage(), 0);
            }
        }

        // calculate next update interval
        $this->UpdateTimerInterval('UpdateTimer', 0, 0, 30);
    }

    /**
     * Import birthdays data.
     *
     * @param string $value Base64 coded data.
     */
    protected function OnImportData($value) {
        $this->ImportICS($value);
    }

    /**
     * Clear the selected days list.
     *
     * @param string $property property name.
     */
    protected function OnDeleteDays($property) {
        $this->SendDebug(__FUNCTION__, $property);
        $data = [];
        $this->UpdateFormField($property, 'values', json_encode($data));
    }

    /**
     * This function will be called by the hook control. Visibility should be protected!
     */
    protected function ProcessHookData() {
        //$this->SendDebug(__FUNCTION__, $_GET);
        $export = isset($_GET['export']) ? $_GET['export'] : '';
        //$this->SendDebug(__FUNCTION__, 'Export: ' . $export);
        $property = '';
        $filename = '';
        switch ($export) {
            case 'BD':
                $property = 'Waste';
                $filename = $this->Translate('waste.ics');
                break;

            default:
                return;
        }
        // get the current entries
        $this->SendDebug(__FUNCTION__, $this->ReadPropertyString($property));
        $list = json_decode($this->ReadPropertyString($property), true);
        if (empty($list) || !is_array($list)) {
            $list = [];
        }
        // build value list
        $entry = [];
        foreach ($list as $key => $item) {
            if (is_array($item)) {
                $dt = json_decode($item['Date'], true);
                $bd = $dt['day'] . '.' . $dt['month'] . '.' . $dt['year'];
                $entry[] = [$bd, $item['Name']];
            }
        }
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');
        // output line by line
        foreach ($entry as $fields) {
            fputcsv($output, $fields);
        }
    }


    /**
     * Lookup for garbage can & container
     */
    private function LookupDays(int $ts, string $property) {
        
        $year = date('Y', $ts);
        $day = date('j', $ts);
        $mon = date('n', $ts);
        
        // get the current entries
        $list = json_decode($this->ReadPropertyString($property), true);
        if (empty($list) || !is_array($list)) {
            $list = [];
        }
        // build value list
        $entry = [];
        foreach ($list as $key => $item) {
            if (is_array($item)) {
                $dt = json_decode($item['Date'], true);
                if ($day == $dt['day'] && $mon == $dt['month']) {
                    $date = $dt['day'] . '.' . $dt['month'] . '.' . $dt['year'];
                    $years = $year - $dt['year'];
                    $entry[] = ['date' => $date, 'years' => $years, 'name' => $item['Name']];
                }
            }
        }
        return $entry;
    }

    /**
     * Format a given array to a string.
     *
     * @param array $item Date event item
     * @param string $format Format string
     */
    private function FormatDay(array $item, $format) {
        $now = date('d.m.Y', time());
        $output = str_replace('%E', $item['date'], $format);
        $output = str_replace('%Y', $item['years'], $output);
        $output = str_replace('%N', $item['name'], $output);
        $output = str_replace('%D', $now, $output);
        return $output;
    }

    /**
     * Update specific days-variable / dashboard.
     *
     * @param array $property Day property idents.
     * @param array $date Day items array.
     */
    private function UpdateDay(array $property, array $date) {
        $this->SendDebug(__FUNCTION__, '');

        $this->SendDebug(__FUNCTION__, 'prop: '.$property, 0);
        $this->SendDebug(__FUNCTION__, 'data: '.$date, 0);
 
        // variable
        $variable = $this->ReadPropertyInteger($property[7]);
        // seperator
        $separator = $this->ReadPropertyString($property[8]);
        // date array
        $ident = substr($property[1], 0, -1);
        $items = $date[$ident];
        $length = count($items);
        $lines = '';
        $index = 1;
  
        $ident = substr($property[1], 0, -1);
        $items = $date[$ident];
        $length = count($items);
        $lines = '';
        $index = 1;
        // iterate
        foreach ($items as $item) {
            // format date item
            $output = $this->FormatDay($item, $format);

            // collect for variable
            if ($index < $length) {
                $lines .= $output . $separator;
            } else {
                $lines .= $output;
            }
            $index++;
        }

        // write to variable
        if ($variable) {
            $this->SetValueString($ident, $lines);
        }
    }

    /** 
    * Function is to get all the contents from ics and explode all the datas according to the events and its sections 
    *
    * @param string $filecontent Name of the list element
    */
    private function getIcsEvents(string $filecontent) {

        $this->SendDebug(__FUNCTION__, $filecontent, 0);

        $icsDates = array();
        /* Explode the ICs Data to get datas as array according to string ‘BEGIN:’ */
        $icsData = explode( "BEGIN:", $filecontent );

        /* Iterating the icsData value to make all the start end dates as sub array */
        foreach( $icsData as $key => $value ) {
            $icsDatesMeta[$key] = explode("\n", $value);
        }
        /* Itearting the Ics Meta Value */
        foreach( $icsDatesMeta as $key => $value ) {
            foreach( $value as $subKey => $subValue ) {
                /* to get ics events in proper order */
                $icsDates = $this->getIcsDates($key, $subKey, $subValue, $icsDates);
            }
        }
        return $icsDates;
    }

    /**
     * Get ICS data and extract dates from iCal format.
     * 
     * @param string $value Data to import (base64 coded)
     */
    private function ImportICS(string $value) {
        $GreyArr   = array();
        $GreenArr  = array();
        $BrownArr  = array();
        $YellowArr = array();
        $GlasArr   = array();

        $IcsData = base64_decode($value);

        $IcsEvents = $this->getIcsEvents($IcsData);
    
        unset( $IcsEvents[1] );
        
        foreach( $IcsEvents as $icsEvent) {
            // Get start date
            $startdate = isset( $icsEvent ['DTSTART;VALUE=DATE'] ) ? $icsEvent ['DTSTART;VALUE=DATE'] : $icsEvent ['DTSTART'];
            $eventName = $icsEvent['SUMMARY'];

            //$date_conv = DateTime::createFromFormat('Ymd', $startdate);
            //$date = $date_conv->format('d.m.Y');

            $date = substr($startdate, 6, 2).'.'.substr($startdate, 4, 2).'.'.substr($startdate, 0, 4);

            switch(substr($eventName, 10, 4)) {        
                case "Papi":    
                    $GreenArr[] .= $date ; 
                    break;
                case "Rest":
                    $GreyArr[] .= $date; 
                    break;
                case "Biom":    
                    $BrownArr[] .= $date; 
                    break;
                case "Wert":    
                    $YellowArr[] .= $date;
                    break;
                case "Glas":    
                    $GlasArr[] .= $date; 
                    break;
            }
        }

        //if ($this->ReadPropertyBoolean("cbUpdGrey")) {
            $this->UpdateListData('ListGrey', $GreyArr);
        //}

        //if ($this->ReadPropertyBoolean("cbUpdGreen")) {
            $this->UpdateListData('ListGreen', $GreenArr);
        //}

        //if ($this->ReadPropertyBoolean("cbUpdBrown")) {
            $this->UpdateListData('ListBrown', $BrownArr);
        //}

        //if ($this->ReadPropertyBoolean("cbUpdYellow")) {
            $this->UpdateListData('ListYellow', $YellowArr);
        //}

        //if ($this->ReadPropertyBoolean("cbUpdGlas")) {
            $this->UpdateListData('ListGlas', $GlasArr);
        //}
    }


    /** 
    * Update Listdata in form
    *
    * @param string $property   property to update values in form
    * @param string $newDates   new dates
    */
    private function UpdateListData($property, $newDates) {
        //get current date
        $curDates = json_decode($this->ReadPropertyString($property), true);
        if (empty($curDates) || !is_array($curDates)) {
            $curDates = [];
        }

        //build value list
        $entry = [];
        sort($newDates);
        foreach ($newDates as $key => $item) {
            $dt = date_parse($item);
            $bd = '{"year":' . $dt['year'] . ',"month":' . $dt['month'] . ',"day":' . $dt['day'] . '}';
            $entry[] = ['Date' => $bd];
        }   
        //merge both
        $newDates = array_merge($curDates, $entry);
        //remove multi dimension
        $newDates = array_map('serialize', $newDates);
        //remove duplicates
        $newDates = array_unique($newDates);
        //back to multidimension array
        $newDates = array_map('unserialize', $newDates);
        // remove index key
        $newDates = array_values($newDates);
        
        // Update list values
        $this->UpdateFormField($property, 'values', json_encode($newDates));
    }

    /**
     *  funcion is to avaid the elements wich is not having the proper start, end  and summary informations
     * 
     * @param string $key       key
     * @param string $subkey    sub key
     * @param string $subvalue  value
     * @param string $icsDates  ics dates
     */
    private function getIcsDates($key, $subKey, $subValue, $icsDates) {
        if ($key != 0 && $subKey == 0) {
            $icsDates [$key] ["BEGIN"] = $subValue;
        } else {
            $subValueArr = explode ( ":", $subValue, 2 );
            if (isset ( $subValueArr [1] )) {
                $icsDates [$key] [$subValueArr [0]] = $subValueArr [1];
            }
        }
        return $icsDates;
    }

    /**
     * Update a boolean value.
     *
     * @param string $ident Ident of the boolean variable
     * @param bool   $value Value of the boolean variable
     */
    private function SetValueBoolean(string $ident, bool $value) {
        $id = $this->GetIDForIdent($ident);
        SetValueBoolean($id, $value);
    }

    /**
     * Update a string value.
     *
     * @param string $ident Ident of the string variable
     * @param string $value Value of the string variable
     */
    private function SetValueString(string $ident, string $value) {
        $id = $this->GetIDForIdent($ident);
        SetValueString($id, $value);
    }

    /**
     * Update a integer value.
     *
     * @param string $ident Ident of the integer variable
     * @param int    $value Value of the integer variable
     */
    private function SetValueInteger(string $ident, int $value) {
        $id = $this->GetIDForIdent($ident);
        SetValueInteger($id, $value);
    }
}
