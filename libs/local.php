<?php

declare(strict_types=1);

trait WaremaWMSLocalLib
{
    public static $IS_SERVERERROR = IS_EBASE + 10;
    public static $IS_HTTPERROR = IS_EBASE + 11;
    public static $IS_INVALIDDATA = IS_EBASE + 12;
    public static $IS_APPFAIL = IS_EBASE + 13;

    private function GetFormStatus()
    {
        $formStatus = $this->GetCommonFormStatus();

        $formStatus[] = ['code' => self::$IS_SERVERERROR, 'icon' => 'error', 'caption' => 'Instance is inactive (server error)'];
        $formStatus[] = ['code' => self::$IS_HTTPERROR, 'icon' => 'error', 'caption' => 'Instance is inactive (http error)'];
        $formStatus[] = ['code' => self::$IS_INVALIDDATA, 'icon' => 'error', 'caption' => 'Instance is inactive (invalid data)'];
        $formStatus[] = ['code' => self::$IS_APPFAIL, 'icon' => 'error', 'caption' => 'Instance is inactive (appliance failure)'];

        return $formStatus;
    }

    public static $STATUS_INVALID = 0;
    public static $STATUS_VALID = 1;
    public static $STATUS_RETRYABLE = 2;

    private function CheckStatus()
    {
        switch ($this->GetStatus()) {
            case IS_ACTIVE:
                $class = self::$STATUS_VALID;
                break;
            case self::$IS_SERVERERROR:
            case self::$IS_HTTPERROR:
            case self::$IS_INVALIDDATA:
                $class = self::$STATUS_RETRYABLE;
                break;
            default:
                $class = self::$STATUS_INVALID;
                break;
        }

        return $class;
    }

    private static $STATE_OK = 0;
    private static $STATE_CHANNEL_UNREACHABLE = 1;
    private static $STATE_GATEWAY_UNREACHABLE = 2;
    private static $STATE_WIND_ALARM = 3;
    private static $STATE_RAIN_ALARM = 4;
    private static $STATE_ERROR = 9;

    private function StateMapping()
    {
        return [
            self::$STATE_OK                  => 'Ok',
            self::$STATE_CHANNEL_UNREACHABLE => 'No response',
            self::$STATE_GATEWAY_UNREACHABLE => 'Gateway unreachable',
            self::$STATE_WIND_ALARM          => 'Wind alarm',
            self::$STATE_RAIN_ALARM          => 'Rain alarm',
            self::$STATE_ERROR               => 'Error',
        ];
    }

    private function DecodeState($state)
    {
        $stateMap = $this->StateMapping();
        if (isset($stateMap[$state])) {
            $s = $this->Translate($stateMap[$state]);
        } else {
            $s = $this->Translate('Unknown state') . ' ' . $state;
        }
        return $s;
    }

    private static $CONTROL_STOP = 0;
    private static $CONTROL_UP = 1;
    private static $CONTROL_DOWN = 2;

    private static $ACTIVITY_STAND = 0;
    private static $ACTIVITY_MOVES = 1;

    private function InstallVarProfiles(bool $reInstall = false)
    {
        if ($reInstall) {
            $this->SendDebug(__FUNCTION__, 'reInstall=' . $this->bool2str($reInstall), 0);
        }

        $associations = [];
        $maps = $this->StateMapping();
        foreach ($maps as $u => $e) {
            $associations[] = ['Wert' => $u, 'Name' => $this->Translate($e), 'Farbe' => -1];
        }
        $this->CreateVarProfile('WaremaWMS.State', VARIABLETYPE_INTEGER, '', 0, 0, 0, 0, '', $associations, $reInstall);

        $associations = [
            ['Wert' => self::$CONTROL_UP, 'Name' => $this->Translate('Retract'), 'Farbe' => -1],
            ['Wert' => self::$CONTROL_STOP, 'Name' => $this->Translate('Stop'), 'Farbe' => -1],
            ['Wert' => self::$CONTROL_DOWN, 'Name' => $this->Translate('Extend'), 'Farbe' => -1],
        ];
        $this->CreateVarProfile('WaremaWMS.ControlExtendRetract', VARIABLETYPE_INTEGER, '', 0, 0, 0, 0, '', $associations, $reInstall);

        $associations = [
            ['Wert' => self::$CONTROL_UP, 'Name' => $this->Translate('Up'), 'Farbe' => -1],
            ['Wert' => self::$CONTROL_STOP, 'Name' => $this->Translate('Stop'), 'Farbe' => -1],
            ['Wert' => self::$CONTROL_DOWN, 'Name' => $this->Translate('Down'), 'Farbe' => -1],
        ];
        $this->CreateVarProfile('WaremaWMS.ControlUpDown', VARIABLETYPE_INTEGER, '', 0, 0, 0, 0, '', $associations, $reInstall);

        $associations = [
            ['Wert' => self::$CONTROL_UP, 'Name' => $this->Translate('Open'), 'Farbe' => -1],
            ['Wert' => self::$CONTROL_STOP, 'Name' => $this->Translate('Stop'), 'Farbe' => -1],
            ['Wert' => self::$CONTROL_DOWN, 'Name' => $this->Translate('Close'), 'Farbe' => -1],
        ];
        $this->CreateVarProfile('WaremaWMS.ControlOpenClose', VARIABLETYPE_INTEGER, '', 0, 0, 0, 0, '', $associations, $reInstall);

        $associations = [
            ['Wert' => self::$ACTIVITY_STAND, 'Name' => $this->Translate('Stand'), 'Farbe' => -1],
            ['Wert' => self::$ACTIVITY_MOVES, 'Name' => $this->Translate('Moves'), 'Farbe' => -1],
        ];
        $this->CreateVarProfile('WaremaWMS.Activity', VARIABLETYPE_INTEGER, '', 0, 0, 0, 0, '', $associations, $reInstall);

        $this->CreateVarProfile('WaremaWMS.Position', VARIABLETYPE_INTEGER, ' %', 0, 100, 1, 0, 'Intensity', [], $reInstall);
        $this->CreateVarProfile('WaremaWMS.Percentage', VARIABLETYPE_INTEGER, ' %', 0, 100, 1, 0, 'Intensity', [], $reInstall);

        $this->CreateVarProfile('WaremaWMS.Rotation', VARIABLETYPE_INTEGER, ' °', -127, 127, 1, 0, 'Intensity', [], $reInstall);
    }

    private static $LANG_DE = 0;
    private static $LANG_EN = 1;
    private static $LANG_FR = 2;
    private static $LANG_NO = 3;
    private static $LANG_SE = 4;
    private static $LANG_IT = 5;
    private static $LANG_ES = 6;

    private function LangMapping()
    {
        return [
            self::$LANG_DE => 'German',
            self::$LANG_EN => 'English',
            self::$LANG_FR => 'French',
            self::$LANG_NO => 'Norwegian',
            self::$LANG_SE => 'Swedish',
            self::$LANG_IT => 'Italian',
            self::$LANG_ES => 'Spanish',
        ];
    }

    private function LangAsOptions()
    {
        $maps = $this->LangMapping();
        $opts = [];
        foreach ($maps as $u => $e) {
            $opts[] = [
                'caption' => $e,
                'value'   => $u,
            ];
        }
        return $opts;
    }

    private function DecodeLang($lang)
    {
        $langMap = $this->LangMapping();
        if (isset($langMap[$lang])) {
            $s = $this->Translate($langMap[$lang]);
        } else {
            $s = $this->Translate('Unknown language') . ' ' . $lang;
        }
        return $s;
    }

    private static $PRODUCT_VENETIAN_BLIND = 0;
    private static $PRODUCT_AWNING = 1;
    private static $PRODUCT_SHUTTER_BLIND = 2;
    private static $PRODUCT_SLAT_ROOF = 3;
    private static $PRODUCT_WINDOW = 4;
    private static $PRODUCT_SWITCH = 5;
    private static $PRODUCT_DIMMER = 6;
    private static $PRODUCT_UNKNOWN = 999;

    private function ProductMapping()
    {
        return [
            self::$PRODUCT_VENETIAN_BLIND => 'Venetian blind',
            self::$PRODUCT_AWNING         => 'Awning',
            self::$PRODUCT_SHUTTER_BLIND  => 'Roller shutter / blind',
            self::$PRODUCT_SLAT_ROOF      => 'Slat roof',
            self::$PRODUCT_WINDOW         => 'Window',
            self::$PRODUCT_SWITCH         => 'Switch',
            self::$PRODUCT_DIMMER         => 'Dimmer',
            self::$PRODUCT_UNKNOWN        => 'Unknown product',
        ];
    }

    private function ProductAsOptions()
    {
        $maps = $this->ProductMapping();
        $opts = [];
        foreach ($maps as $u => $e) {
            $opts[] = [
                'caption' => $e,
                'value'   => $u,
            ];
        }
        return $opts;
    }

    private function DecodeProduct($product)
    {
        $productMap = $this->ProductMapping();
        if (isset($productMap[$product])) {
            $s = $this->Translate($productMap[$product]);
        } else {
            $s = $this->Translate('Unknown product') . ' ' . $product;
        }
        return $s;
    }

    private static $INTERFACE_WEBCONTROL = 0;
    private static $INTERFACE_WEBCONTROLPRO = 1;

    private function InterfaceMapping()
    {
        return [
            self::$INTERFACE_WEBCONTROL    => 'WebControl',
            self::$INTERFACE_WEBCONTROLPRO => 'WebControl Pro',
        ];
    }

    private function InterfaceAsOptions()
    {
        $maps = $this->InterfaceMapping();
        $opts = [];
        foreach ($maps as $u => $e) {
            $opts[] = [
                'caption' => $e,
                'value'   => $u,
            ];
        }
        return $opts;
    }

    private static $ACTION_TYPE_PERCENTAGE = 0;
    private static $ACTION_TYPE_PERCENTAGE_DETLA = 1;
    private static $ACTION_TYPE_ROTATION = 2;
    private static $ACTION_TYPE_ROTATION_DELTA = 3;
    private static $ACTION_TYPE_SWITCH = 4;
    private static $ACTION_TYPE_TOGGLE = 5;
    private static $ACTION_TYPE_STOP = 6;
    private static $ACTION_TYPE_IMPULSE = 7;
    private static $ACTION_TYPE_IDENTIFY = 8;
    private static $ACTION_TYPE_ENUMERATION = 9;
    private static $ACTION_TYPE_UNKNOWN = 999;

    private function ActionTypeMapping()
    {
        return [
            self::$ACTION_TYPE_PERCENTAGE       => 'Positioning',
            self::$ACTION_TYPE_PERCENTAGE_DETLA => 'Positioning delta',
            self::$ACTION_TYPE_ROTATION         => 'Rotation',
            self::$ACTION_TYPE_ROTATION_DELTA   => 'Rotation delta',
            self::$ACTION_TYPE_SWITCH           => 'Switch',
            self::$ACTION_TYPE_TOGGLE           => 'Toggle',
            self::$ACTION_TYPE_STOP             => 'Stop',
            self::$ACTION_TYPE_IMPULSE          => 'Step',
            self::$ACTION_TYPE_IDENTIFY         => 'Identify',
            self::$ACTION_TYPE_ENUMERATION      => 'Enumeration',
            self::$ACTION_TYPE_UNKNOWN          => 'Unknown action type',
        ];
    }

    private function DecodeActionType($actionType)
    {
        $actionTypeMap = $this->ActionTypeMapping();
        if (isset($actionTypeMap[$actionType])) {
            $s = $this->Translate($actionTypeMap[$actionType]);
        } else {
            $s = $this->Translate('Unknown action type') . ' ' . $actionType;
        }
        return $s;
    }

    private static $ACTION_DESC_AWNING_DRIVE = 0;
    private static $ACTION_DESC_VALANCE_DRIVE = 1;
    private static $ACTION_DESC_SLAT_DRIVE = 2;
    private static $ACTION_DESC_SLAT_ROTATE = 3;
    private static $ACTION_DESC_SHUTTER_BLIND_DRIVE = 4;
    private static $ACTION_DESC_WINDOW_DRIVE = 5;
    private static $ACTION_DESC_LIGHT_SWITCH = 6;
    private static $ACTION_DESC_LOAD_SWITCH = 7;
    private static $ACTION_DESC_LIGHT_DIMMING = 8;
    private static $ACTION_DESC_LOAD_DIMMING = 9;
    private static $ACTION_DESC_LIGHT_TOGGLE = 10;
    private static $ACTION_DESC_LOAD_TOGGLE = 11;
    private static $ACTION_DESC_MANUAL_COMMAND = 12;
    private static $ACTION_DESC_IDENTIFY = 13;
    private static $ACTION_DESC_UNKNOWN = 999;

    private function ActionDescMapping()
    {
        return [
            self::$ACTION_DESC_AWNING_DRIVE        => 'Awning drive',
            self::$ACTION_DESC_VALANCE_DRIVE       => 'Valance drive',
            self::$ACTION_DESC_SLAT_DRIVE          => 'Slat drive',
            self::$ACTION_DESC_SLAT_ROTATE         => 'Slat rotate',
            self::$ACTION_DESC_SHUTTER_BLIND_DRIVE => 'Roller shutter / blind drive',
            self::$ACTION_DESC_WINDOW_DRIVE        => 'Window drive',
            self::$ACTION_DESC_LIGHT_SWITCH        => 'Light switch',
            self::$ACTION_DESC_LOAD_SWITCH         => 'Load switch',
            self::$ACTION_DESC_LIGHT_DIMMING       => 'Light dimming',
            self::$ACTION_DESC_LOAD_DIMMING        => 'Load dimming',
            self::$ACTION_DESC_LIGHT_TOGGLE        => 'Light toggle',
            self::$ACTION_DESC_LOAD_TOGGLE         => 'Load toggle',
            self::$ACTION_DESC_MANUAL_COMMAND      => 'Manual Command',
            self::$ACTION_DESC_IDENTIFY            => 'Identify',
            self::$ACTION_DESC_UNKNOWN             => 'Unknown action description',
        ];
    }

    private function DecodeActionDesc($actionDesc)
    {
        $actionDescMap = $this->ActionDescMapping();
        if (isset($actionDescMap[$actionDesc])) {
            $s = $this->Translate($actionDescMap[$actionDesc]);
        } else {
            $s = $this->Translate('Unknown action description') . ' ' . $actionDesc;
        }
        return $s;
    }

    private static $DRIVING_CLAUSE_NONE = 0;
    private static $DRIVING_CLAUSE_SUN = 1;
    private static $DRIVING_CLAUSE_DUSK_DAWN = 2;
    private static $DRIVING_CLAUSE_WIND = 3;
    private static $DRIVING_CLAUSE_RAIN = 4;
    private static $DRIVING_CLAUSE_ICE = 5;
    private static $DRIVING_CLAUSE_TEMPERATURE = 6;
    private static $DRIVING_CLAUSE_SWITCHING_TIME = 7;
    private static $DRIVING_CLAUSE_SCENE = 8;
    private static $DRIVING_CLAUSE_CONTROL_MODE = 9;
    private static $DRIVING_CLAUSE_MANUAL = 10;
    private static $DRIVING_CLAUSE_SAFETY = 11;
    private static $DRIVING_CLAUSE_CONTACT = 12;
    private static $DRIVING_CLAUSE_CENTRAL_COMMAND = 13;
    private static $DRIVING_CLAUSE_UNKNOWN = 999;

    private function DrivingClauseMapping()
    {
        return [
            self::$DRIVING_CLAUSE_NONE            => 'None',
            self::$DRIVING_CLAUSE_SUN             => 'Sun',
            self::$DRIVING_CLAUSE_DUSK_DAWN       => 'Dusk/Dawn',
            self::$DRIVING_CLAUSE_WIND            => 'Wind',
            self::$DRIVING_CLAUSE_RAIN            => 'Rain',
            self::$DRIVING_CLAUSE_ICE             => 'Ice',
            self::$DRIVING_CLAUSE_TEMPERATURE     => 'Temperature',
            self::$DRIVING_CLAUSE_SWITCHING_TIME  => 'Switching Time',
            self::$DRIVING_CLAUSE_SCENE           => 'Scene',
            self::$DRIVING_CLAUSE_CONTROL_MODE    => 'Control Mode',
            self::$DRIVING_CLAUSE_MANUAL          => 'Manual',
            self::$DRIVING_CLAUSE_SAFETY          => 'Safety',
            self::$DRIVING_CLAUSE_CONTACT         => 'Contact',
            self::$DRIVING_CLAUSE_CENTRAL_COMMAND => 'Central Command',
            self::$DRIVING_CLAUSE_UNKNOWN         => 'Unknown driving clause',
        ];
    }

    private function DecodeDrivingClause($drivingClause)
    {
        $drivingClauseMap = $this->DrivingClauseMapping();
        if (isset($drivingClauseMap[$drivingClause])) {
            $s = $this->Translate($drivingClauseMap[$drivingClause]);
        } else {
            $s = $this->Translate('Unknown action description') . ' ' . $drivingClause;
        }
        return $s;
    }
}
