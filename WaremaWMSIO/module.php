<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/common.php';
require_once __DIR__ . '/../libs/local.php';  // lokale Funktionen

class WaremaWMSIO extends IPSModule
{
    use WaremaWMS\StubsCommonLib;
    use WaremaWMSLocalLib;

    private static $WC_TEL_RAUM_ANLEGEN = 1;
    private static $WC_RES_RAUM_ANLEGEN = 2;
    private static $WC_TEL_RAUM_ABFRAGEN = 3;				// WebControl_GetDevices()
    private static $WC_RES_RAUM_ABFRAGEN = 4;				// WebControl_GetDevices()
    private static $WC_TEL_RAUMNAMEN_AENDERN = 5;
    private static $WC_RES_RAUMNAMEN_AENDERN = 6;
    private static $WC_TEL_RAUMREIHENFOLGE_AENDERN = 7;
    private static $WC_RES_RAUMREIHENFOLGE_AENDERN = 8;
    private static $WC_TEL_RAUM_LOESCHEN = 9;
    private static $WC_RES_RAUM_LOESCHEN = 10;
    private static $WC_TEL_KANAL_ANLEGEN = 11;
    private static $WC_RES_KANAL_ANLEGEN = 12;
    private static $WC_TEL_KANAL_ABFRAGEN = 13;				// WebControl_GetDevices()
    private static $WC_RES_KANAL_ABFRAGEN = 14;				// WebControl_GetDevices()
    private static $WC_TEL_KANALNAMEN_AENDERN = 15;
    private static $WC_RES_KANALNAMEN_AENDERN = 16;
    private static $WC_TEL_KANALREIHENFOLGE_AENDERN = 17;
    private static $WC_RES_KANALREIHENFOLGE_AENDERN = 18;
    private static $WC_TEL_KANAL_LOESCHEN = 19;
    private static $WC_RES_KANAL_LOESCHEN = 20;
    private static $WC_TEL_RAUM_KOPIEREN = 21;
    private static $WC_RES_RAUM_KOPIEREN = 22;
    private static $WC_TEL_KANAL_IN_RAUM_KOPIEREN = 23;
    private static $WC_RES_KANAL_IN_RAUM_KOPIEREN = 24;
    private static $WC_TEL_AKTOREN_ZUWEISEN = 25;
    private static $WC_RES_AKTOREN_ZUWEISEN = 26;
    private static $WC_TEL_INFRASTRUKTUR_SPEICHERN = 27;
    private static $WC_RES_INFRASTRUKTUR_SPEICHERN = 28;
    private static $WC_TEL_INFRASTRUKTUR_LADEN = 29;
    private static $WC_RES_INFRASTRUKTUR_LADEN = 30;
    private static $WC_TEL_DEF_INFRASTRUKTUR_SPEICHERN = 31;
    private static $WC_RES_DEF_INFRASTRUKTUR_SPEICHERN = 32;
    private static $WC_TEL_KANALBEDIENUNG = 33;				// WebControl_Kanalbedienung()
    private static $WC_RES_KANALBEDIENUNG = 34;				// WebControl_Kanalbedienung()
    private static $WC_TEL_POS_RUECKMELDUNG = 35;			// WebControl_QueryDeviceStatus()
    private static $WC_RES_POS_RUECKMELDUNG = 36;			// WebControl_QueryDeviceStatus()
    private static $WC_TEL_WINKEN = 37;
    private static $WC_RES_WINKEN = 38;
    private static $WC_TEL_PASSWORT_ABFRAGE = 39;
    private static $WC_RES_PASSWORT_ABFRAGE = 40;
    private static $WC_TEL_PASSWORT_AENDERN = 41;
    private static $WC_RES_PASSWORT_AENDERN = 42;
    private static $WC_TEL_AUTOMATIK = 43;
    private static $WC_RES_AUTOMATIK = 44;
    private static $WC_TEL_GRENZWERTE = 45;
    private static $WC_RES_GRENZWERTE = 46;
    private static $WC_TEL_RTC = 47;
    private static $WC_RES_RTC = 48;
    private static $WC_TEL_POLLING = 49;					// WebControl_QueryDeviceStatus()
    private static $WC_RES_POLLING = 50;					// WebControl_QueryDeviceStatus()
    private static $WC_RES_WMS_STACK_BUSY = 51;				// WebControl_Kanalbedienung()
    private static $WC_RES_ERROR_MESSAGE = 52;				// WebControl_HttpRequest()
    private static $WC_TEL_SPRACHE = 61;					// WebControl_GetLanguage()
    private static $WC_RES_SPRACHE = 62;
    private static $WC_TEL_SET_GRENZWERTE = 63;
    private static $WC_RES_SET_GRENZWERTE = 64;
    private static $WC_TEL_SZENE_ANLEGEN = 69;
    private static $WC_RES_SZENE_ANLEGEN = 70;
    private static $WC_TEL_KANAL_SZENE_ABFRAGEN = 71;
    private static $WC_RES_KANAL_SZENE_ABFRAGEN = 72;
    private static $WC_TEL_LESE_MENUETABELLE_FIX = 73;
    private static $WC_RES_LESE_MENUETABELLE_FIX = 74;
    private static $WC_TEL_LESE_MENUETABELLE_PRODUKT_ABH = 75;
    private static $WC_RES_LESE_MENUETABELLE_PRODUKT_ABH = 76;
    private static $WC_TEL_LESE_WMS_PARAMETER = 77;
    private static $WC_RES_LESE_WMS_PARAMETER = 78;
    private static $WC_TEL_SCHREIBE_WMS_PARAMETER = 79;
    private static $WC_RES_SCHREIBE_WMS_PARAMETER = 80;
    private static $WC_TEL_WMS_INDEX_HEADER_1 = 81;
    private static $WC_RES_WMS_INDEX_HEADER_1 = 82;
    private static $WC_TEL_WMS_INDEX_HEADER_2 = 83;
    private static $WC_RES_WMS_INDEX_HEADER_2 = 84;
    private static $WC_TEL_WMS_INDEX_HEADER_3 = 85;
    private static $WC_RES_WMS_INDEX_HEADER_3 = 86;
    private static $WC_TEL_WMS_INDEX_PARAMETERNAME = 87;
    private static $WC_RES_WMS_INDEX_PARAMETERNAME = 88;
    private static $WC_TEL_WMS_RECHTE = 89;
    private static $WC_RES_WMS_RECHTE = 90;
    private static $WC_TEL_WMS_INDEX_PARAMETERTYP = 91;
    private static $WC_RES_WMS_INDEX_PARAMETERTYP = 92;
    private static $WC_TEL_WMS_PARAMETER_DEFAULTWERT = 93;
    private static $WC_RES_WMS_PARAMETER_DEFAULTWERT = 94;
    private static $WC_TEL_GET_WMS_PARAMETER = 95;
    private static $WC_RES_GET_WMS_PARAMETER = 96;
    private static $WC_TEL_SET_WMS_PARAMETER = 97;
    private static $WC_RES_SET_WMS_PARAMETER = 98;
    private static $WC_TEL_GET_WMS_PARAMETER_ZSP = 99;
    private static $WC_RES_GET_WMS_PARAMETER_ZSP = 100;
    private static $WC_TEL_SET_WMS_PARAMETER_ZSP = 101;
    private static $WC_RES_SET_WMS_PARAMETER_ZSP = 102;
    private static $WC_TEL_KOMFORT = 103;
    private static $WC_RES_KOMFORT = 104;

    private static $WC_ERROR_CODE_SD_KARTE = 13;
    private static $WC_ERROR_CODE_MAX_SZENEN = 8;
    private static $WC_ERROR_CODE_MAX_KANAL = 10;
    private static $WC_ERROR_CODE_POLLING_BEFEHL = 32;
    private static $WC_ERROR_CODE_POLLING_KANAL = 33;
    private static $WC_ERROR_CODE_PROJECT_FILE = 35;
    private static $WC_ERROR_CODE_BEREICHINDEX = 41;
    private static $WC_ERROR_CODE_CONTENT_INVALID = 42;
    private static $WC_ERROR_CODE_PANID_INVALID = 43;

    private static $WC_POLL_KANALBEDIENUNG = 0;				// WebControl_Kanalbedienung()
    private static $WC_POLL_POSITION = 1;					// WebControl_QueryDeviceStatus()
    private static $WC_POLL_GRENZWERTE = 2;
    private static $WC_POLL_AKTOREN_ZUWEISEN = 3;
    private static $WC_POLL_AUTOMATIK = 4;
    private static $WC_POLL_WINKEN = 5;
    private static $WC_POLL_SET_GRENZWERTE = 6;
    private static $WC_POLL_READ_MENUETAB_FIX = 7;
    private static $WC_POLL_READ_MENUETAB_PROD = 8;
    private static $WC_POLL_READ_WMS_PARA = 9;
    private static $WC_POLL_WRITE_WMS_PARA = 10;
    private static $WC_POLL_KOMFORT = 11;

    private static $WC_FC_DONT_CARE = 0;
    private static $WC_FC_STOP = 1;
    private static $WC_FC_SOLL_DIREKT = 2;
    private static $WC_FC_SOLL_SICHER = 3;					// WebControl_SendPosition()
    private static $WC_FC_IMPULS_WENDEN_HOCH = 4;
    private static $WC_FC_IMPULS_WENDEN_TIEF = 5;
    private static $WC_FC_HOCH = 6;
    private static $WC_FC_TIEF = 7;
    private static $WC_FC_SZENE_AUSFUEHREN = 8;
    private static $WC_FC_SZENE_LERNEN = 9;
    private static $WC_FC_TOGGELN = 10;
    private static $WC_FC_AUFDIMMEN = 11;
    private static $WC_FC_ABDIMMEN = 12;
    private static $WC_FC_HOCH_M = 13;
    private static $WC_FC_TIEF_M = 14;
    private static $WC_FC_HOCH_V = 15;
    private static $WC_FC_TIEF_V = 16;
    private static $WC_FC_HOCH_VL = 17;
    private static $WC_FC_TIEF_VL = 18;
    private static $WC_FC_HOCH_VR = 19;
    private static $WC_FC_TIEF_VR = 20;
    private static $WC_FC_EIN = 21;
    private static $WC_FC_AUS = 22;
    private static $WC_FC_TASTE_STOP_DIREKT = 23;
    private static $WC_FC_TASTE_STOP_KURZ = 24;
    private static $WC_FC_TASTE_STOP_LANG = 25;
    private static $WC_FC_TASTE_STOP_DOPPELT = 26;
    private static $WC_FC_TASTE_HOCH_DIREKT = 27;
    private static $WC_FC_TASTE_HOCH_KURZ = 28;
    private static $WC_FC_TASTE_HOCH_LANG = 29;
    private static $WC_FC_TASTE_HOCH_DOPPELT = 30;
    private static $WC_FC_TASTE_TIEF_DIREKT = 31;
    private static $WC_FC_TASTE_TIEF_KURZ = 32;
    private static $WC_FC_TASTE_TIEF_LANG = 33;
    private static $WC_FC_TASTE_TIEF_DOPPELT = 34;
    private static $WC_FC_WINKEN = 41;
    private static $WC_FC_WINKEN_VL = 42;
    private static $WC_FC_WINKEN_VR = 43;

    private static $WC_DEF_MAXRAUM = 20;
    private static $WC_DEF_MAXKANAL = 10;

    private static $WCP_RETRY_COUNT = 5;
    private static $WCP_RETRY_DELAY = 500;

    public function __construct(string $InstanceID)
    {
        parent::__construct($InstanceID);

        $this->CommonConstruct(__DIR__);
    }

    public function __destruct()
    {
        $this->CommonDestruct();
    }

    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyBoolean('module_disable', false);

        $this->RegisterPropertyInteger('interface', self::$INTERFACE_WEBCONTROL);
        $this->RegisterPropertyString('host', '');

        $this->RegisterAttributeInteger('command_counter', 1);

        $this->RegisterAttributeString('UpdateInfo', json_encode([]));
        $this->RegisterAttributeString('ModuleStats', json_encode([]));

        $this->InstallVarProfiles(false);
    }

    private function CheckModuleConfiguration()
    {
        $r = [];

        $interface = $this->ReadPropertyInteger('interface');
        $host = $this->ReadPropertyString('host');
        if (in_array($interface, [self::$INTERFACE_WEBCONTROL, self::$INTERFACE_WEBCONTROLPRO]) && $host == '') {
            $this->SendDebug(__FUNCTION__, '"host" is needed', 0);
            $r[] = $this->Translate('Host must be specified');
        }

        return $r;
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $this->MaintainReferences();

        if ($this->CheckPrerequisites() != false) {
            $this->MaintainStatus(self::$IS_INVALIDPREREQUISITES);
            return;
        }

        if ($this->CheckUpdate() != false) {
            $this->MaintainStatus(self::$IS_UPDATEUNCOMPLETED);
            return;
        }

        if ($this->CheckConfiguration() != false) {
            $this->MaintainStatus(self::$IS_INVALIDCONFIG);
            return;
        }

        $module_disable = $this->ReadPropertyBoolean('module_disable');
        if ($module_disable) {
            $this->MaintainStatus(IS_INACTIVE);
            return;
        }

        $this->MaintainStatus(IS_ACTIVE);
    }

    private function GetFormElements()
    {
        $formElements = $this->GetCommonFormElements('Warema WMS I/O');

        if ($this->GetStatus() == self::$IS_UPDATEUNCOMPLETED) {
            return $formElements;
        }

        $formElements[] = [
            'type'    => 'CheckBox',
            'name'    => 'module_disable',
            'caption' => 'Disable instance'
        ];

        $formElements[] = [
            'type'     => 'Select',
            'options'  => $this->InterfaceAsOptions(),
            'name'     => 'interface',
            'caption'  => 'WMS interface'
        ];

        $interface = $this->ReadPropertyInteger('interface');
        if (in_array($interface, [self::$INTERFACE_WEBCONTROL, self::$INTERFACE_WEBCONTROLPRO])) {
            $formElements[] = [
                'type'     => 'ValidationTextBox',
                'name'     => 'host',
                'caption'  => 'Hostname of WebControl',
            ];
        }

        return $formElements;
    }

    private function GetFormActions()
    {
        $formActions = [];

        if ($this->GetStatus() == self::$IS_UPDATEUNCOMPLETED) {
            $formActions[] = $this->GetCompleteUpdateFormAction();

            $formActions[] = $this->GetInformationFormAction();
            $formActions[] = $this->GetReferencesFormAction();

            return $formActions;
        }

        $formActions[] = [
            'type'    => 'Button',
            'caption' => 'Test access',
            'onClick' => 'IPS_RequestAction(' . $this->InstanceID . ', "TestAccess", "");',
        ];

        $items = [];

        $interface = $this->ReadPropertyInteger('interface');
        if ($interface == self::$INTERFACE_WEBCONTROL) {
            $items[] = [
                'type'    => 'RowLayout',
                'items'   => [
                    [
                        'type'    => 'Label',
                        'caption' => 'Decode element "protocol" of url',
                    ],
                    [
                        'type'    => 'ValidationTextBox',
                        'name'    => 'protocol',
                        'caption' => 'String'
                    ],
                    [
                        'type'    => 'Button',
                        'caption' => 'Decode',
                        'onClick' => 'IPS_RequestAction(' . $this->InstanceID . ', "WebControl_DecodeProtocol", json_encode(["protocol" => $protocol]));',
                    ],
                    [
                        'type'    => 'Label',
                    ],
                ],
            ];
        }

        $items[] = $this->GetInstallVarProfilesFormItem();

        $formActions[] = [
            'type'      => 'ExpansionPanel',
            'caption'   => 'Expert area',
            'expanded'  => false,
            'items'     => $items,
        ];

        $formActions[] = [
            'type'      => 'ExpansionPanel',
            'caption'   => 'Test area',
            'expanded'  => false,
            'items'     => [
                [
                    'type'    => 'TestCenter',
                ],
            ]
        ];

        $formActions[] = $this->GetInformationFormAction();
        $formActions[] = $this->GetReferencesFormAction();

        return $formActions;
    }

    public function RequestAction($ident, $value)
    {
        if ($this->CommonRequestAction($ident, $value)) {
            return;
        }

        switch ($ident) {
            case 'TestAccess':
                $this->TestAccess();
                break;
            case 'WebControl_DecodeProtocol':
                $this->WebControl_DecodeProtocol($value);
                break;
            default:
                $this->SendDebug(__FUNCTION__, 'invalid ident ' . $ident, 0);
                break;
        }
    }

    private function TestAccess()
    {
        $interface = $this->ReadPropertyInteger('interface');
        switch ($interface) {
            case self::$INTERFACE_WEBCONTROL:
                $s = '- ' . $this->Translate('Warema WMS WebControl configuration') . ' -' . PHP_EOL;
                $s .= PHP_EOL;

                $lang = $this->WebControl_GetLanguage();
                if ($lang != -1) {
                    $s .= $this->Translate('Language') . ': ' . $this->DecodeLang($lang) . PHP_EOL;

                    $s .= $this->Translate('Devices') . ': ' . PHP_EOL;
                    $r = $this->GetDevices();
                    $devices = isset($r['Data']) ? $r['Data'] : false;
                    if (is_array($devices) && count($devices)) {
                        foreach ($devices as $device) {
                            $this->SendDebug(__FUNCTION__, 'device=' . print_r($device, true), 0);
                            $s .= ' [' . $device['room_id'] . '/' . $device['channel_id'] . '] ' . $device['room_name'] . '/' . $device['channel_name'];
                            $s .= ': ' . $this->DecodeProduct($device['product']);
                            $s .= PHP_EOL;
                        }
                    } else {
                        $s .= ' ' . $this->Translate('no configured devices') . PHP_EOL;
                    }
                } else {
                    $s .= $this->Translate('access failed') . PHP_EOL;
                }
                $this->PopupMessage($s);
                break;
            case self::$INTERFACE_WEBCONTROLPRO:
                $s = '- ' . $this->Translate('Warema WMS WebControl Pro configuration') . ' -' . PHP_EOL;
                $s .= PHP_EOL;

                $r = $this->WebControlPro_SendPing();
                $s .= $this->Translate('Status') . ': ' . $this->DecodeState($r['State']) . PHP_EOL;
                $s .= PHP_EOL;

                $s .= $this->Translate('Devices') . ': ' . PHP_EOL;
                $r = $this->GetDevices();
                $devices = isset($r['Data']) ? $r['Data'] : false;
                if (is_array($devices) && count($devices)) {
                    foreach ($devices as $device) {
                        $this->SendDebug(__FUNCTION__, 'device=' . print_r($device, true), 0);
                        $s .= ' [' . $device['room_id'] . '/' . $device['channel_id'] . '] ' . $device['room_name'] . '/' . $device['channel_name'];
                        $s .= ': ' . $this->DecodeProduct($device['product']);
                        $s .= PHP_EOL;
                    }
                } else {
                    $s .= ' ' . $this->Translate('no configured devices') . PHP_EOL;
                }

                $this->PopupMessage($s);
                break;
        }
    }

    private function GetDevices()
    {
        $interface = $this->ReadPropertyInteger('interface');
        switch ($interface) {
            case self::$INTERFACE_WEBCONTROL:
                $ret = $this->WebControl_GetDevices();
                break;
            case self::$INTERFACE_WEBCONTROLPRO:
                $ret = $this->WebControlPro_GetDevices();
                break;
            default:
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => 'command unsupported for interface ' . $interface,
                ];
                break;
        }
        return $ret;
    }

    private function QueryDeviceStatus($jdata)
    {
        $interface = $this->ReadPropertyInteger('interface');
        switch ($interface) {
            case self::$INTERFACE_WEBCONTROL:
                $ret = $this->WebControl_QueryDeviceStatus($jdata);
                break;
            case self::$INTERFACE_WEBCONTROLPRO:
                $ret = $this->WebControlPro_QueryDeviceStatus($jdata);
                break;
            default:
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => 'command unsupported for interface ' . $interface,
                ];
                break;
        }
        return $ret;
    }

    private function SendStop($jdata)
    {
        $interface = $this->ReadPropertyInteger('interface');
        switch ($interface) {
            case self::$INTERFACE_WEBCONTROL:
                $ret = $this->WebControl_Kanalbedienung(self::$WC_FC_STOP, $jdata);
                break;
            case self::$INTERFACE_WEBCONTROLPRO:
                $ret = $this->WebControlPro_SendAction($jdata);
                break;
            default:
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => 'command unsupported for interface ' . $interface,
                ];
                break;
        }
        return $ret;
    }

    private function SendUp($jdata)
    {
        $interface = $this->ReadPropertyInteger('interface');
        switch ($interface) {
            case self::$INTERFACE_WEBCONTROL:
                $ret = $this->WebControl_Kanalbedienung(self::$WC_FC_HOCH, $jdata);
                break;
            case self::$INTERFACE_WEBCONTROLPRO:
                $ret = $this->WebControlPro_SendAction($jdata);
                break;
            default:
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => 'command unsupported for interface ' . $interface,
                ];
                break;
        }
        return $ret;
    }

    private function SendDown($jdata)
    {
        $interface = $this->ReadPropertyInteger('interface');
        switch ($interface) {
            case self::$INTERFACE_WEBCONTROL:
                $ret = $this->WebControl_Kanalbedienung(self::$WC_FC_TIEF, $jdata);
                break;
            case self::$INTERFACE_WEBCONTROLPRO:
                $ret = $this->WebControlPro_SendAction($jdata);
                break;
            default:
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => 'command unsupported for interface ' . $interface,
                ];
                break;
        }
        return $ret;
    }

    private function SendPosition($jdata)
    {
        $interface = $this->ReadPropertyInteger('interface');
        switch ($interface) {
            case self::$INTERFACE_WEBCONTROL:
                $ret = $this->WebControl_SendPosition($jdata);
                break;
            case self::$INTERFACE_WEBCONTROLPRO:
                $ret = $this->WebControlPro_SendAction($jdata);
                break;
            default:
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => 'command unsupported for interface ' . $interface,
                ];
                break;
        }
        return $ret;
    }

    private function SendRotation($jdata)
    {
        $interface = $this->ReadPropertyInteger('interface');
        switch ($interface) {
            case self::$INTERFACE_WEBCONTROLPRO:
                $ret = $this->WebControlPro_SendAction($jdata);
                break;
            default:
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => 'command unsupported for interface ' . $interface,
                ];
                break;
        }
        return $ret;
    }

    private function SendSwitch($jdata)
    {
        $interface = $this->ReadPropertyInteger('interface');
        switch ($interface) {
            case self::$INTERFACE_WEBCONTROLPRO:
                $ret = $this->WebControlPro_SendAction($jdata);
                break;
            default:
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => 'command unsupported for interface ' . $interface,
                ];
                break;
        }
        return $ret;
    }

    private function SendDim($jdata)
    {
        $interface = $this->ReadPropertyInteger('interface');
        switch ($interface) {
            case self::$INTERFACE_WEBCONTROLPRO:
                $ret = $this->WebControlPro_SendAction($jdata);
                break;
            default:
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => 'command unsupported for interface ' . $interface,
                ];
                break;
        }
        return $ret;
    }

    public function ForwardData($data)
    {
        if ($this->CheckStatus() == self::$STATUS_INVALID) {
            $this->SendDebug(__FUNCTION__, $this->GetStatusText() . ' => skip', 0);
            return;
        }

        $jdata = json_decode($data, true);
        $this->SendDebug(__FUNCTION__, 'data=' . print_r($jdata, true), 0);

        $ret = '';
        if (isset($jdata['Function'])) {
            switch ($jdata['Function']) {
                case 'GetDevices':
                    $ret = json_encode($this->GetDevices());
                    break;
                case 'QueryDeviceStatus':
                    $ret = json_encode($this->QueryDeviceStatus($jdata));
                    break;
                case 'SendStop':
                    $ret = json_encode($this->SendStop($jdata));
                    break;
                case 'SendUp':
                    $ret = json_encode($this->SendUp($jdata));
                    break;
                case 'SendDown':
                    $ret = json_encode($this->SendDown($jdata));
                    break;
                case 'SendPosition':
                    $ret = json_encode($this->SendPosition($jdata));
                    break;
                case 'SendRotation':
                    $ret = json_encode($this->SendRotation($jdata));
                    break;
                case 'SendSwitch':
                    $ret = json_encode($this->SendSwitch($jdata));
                    break;
                case 'SendDim':
                    $ret = json_encode($this->SendDim($jdata));
                    break;
                default:
                    $this->SendDebug(__FUNCTION__, 'unknown function "' . $jdata['Function'] . '"', 0);
                    break;
                }
        } else {
            $this->SendDebug(__FUNCTION__, 'unknown message-structure', 0);
        }

        $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
        return $ret;
    }

    private function build_url($url, $params)
    {
        $n = 0;
        if (is_array($params)) {
            foreach ($params as $param => $value) {
                $url .= ($n++ ? '&' : '?') . $param . '=' . rawurlencode(strval($value));
            }
        }
        return $url;
    }

    private function build_header($headerfields)
    {
        $header = [];
        foreach ($headerfields as $key => $value) {
            $header[] = $key . ': ' . $value;
        }
        return $header;
    }

    // WebControl

    private function WebControl_DecodeProtocol($params)
    {
        $jparams = json_decode($params, true);
        $protocol = isset($jparams['protocol']) ? $jparams['protocol'] : '';

        $msg = $this->Translate('Element "protocol"') . ' "' . $protocol . '"' . PHP_EOL;
        $msg .= PHP_EOL;

        $msg .= $this->Translate('Structure') . PHP_EOL;
        $l = strlen($protocol);
        for ($i = 0; $i < $l; $i += 2) {
            $s = substr($protocol, $i, 2);
            $n = $i / 2;
            $msg .= sprintf(' %02d: 0x%s %03d', $n, $s, hexdec($s));
            switch ($n) {
                case 0:
                    $msg .= ' (' . $this->Translate('Command header') . ')';
                    break;
                case 1:
                    $msg .= ' (' . $this->Translate('Command counter') . ')';
                    break;
                case 2:
                    $msg .= ' (' . $this->Translate('Payload length') . ')';
                    break;
                default:
                    $msg .= ' (' . $this->Translate('Payload') . ' ' . strval($n - 2) . ')';
                    break;
            }
            $msg .= PHP_EOL;
        }

        $this->PopupMessage($msg);
    }

    private function WebControl_BuildCommand($payload)
    {
        /*
         * bbccllp1p2...
         *   bb=Befehlsheader - fix 0x90
         *   cc==Befehlzähler (1..2⅘4)
         *   ll=payload-length (1..32)
         *   p1=payload1
         *   p2=payload2
         *   ...
         */

        if ($payload == false) {
            $payload = [];
        }
        $l = count($payload);
        if ($l < 1 || $l > 32) {
            $this->SendDebug(__FUNCTION__, 'length of payload must be 1..32, payload=' . print_r($payload, true), 0);
            return false;
        }

        # Befehlsheader
        $cmd = sprintf('%02x', 0x90);

        # Befehlscounter
        $c = $this->ReadAttributeInteger('command_counter');
        $c++;
        if ($c < 1 || $c > 254) {
            $c = 1;
        }
        $this->WriteAttributeInteger('command_counter', $c);
        $cmd .= sprintf('%02x', $c);

        # Payload-length
        $cmd .= sprintf('%02x', $l);

        # Payload
        foreach ($payload as $p) {
            $cmd .= sprintf('%02x', $p);
        }

        $this->SendDebug(__FUNCTION__, 'payload=' . implode(', ', $payload) . ' => cmd=' . $cmd, 0);
        return $cmd;
    }

    private function WebControl_HttpRequest($payload)
    {
        $host = $this->ReadPropertyString('host');

        $cmd = $this->WebControl_BuildCommand($payload);
        if ($cmd == false) {
            return false;
        }

        $ts = (int) (microtime(true) * 1000);
        $url = 'http://' . $host . '/protocol.xml?protocol=' . $cmd . '&_=' . strval($ts);

        $this->SendDebug(__FUNCTION__, 'http-get: url=' . $url, 0);

        $header = [
            'Accept: text/html, */*; q=0.01',
            'User-Agent: Symcon',
            'Accept-Language: de-DE,de;q=0.9',
            'X-Requested-With: XMLHttpRequest',
        ];

        $time_start = microtime(true);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $cdata = curl_exec($ch);
        $cerrno = curl_errno($ch);
        $cerror = $cerrno ? curl_error($ch) : '';
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $duration = round(microtime(true) - $time_start, 2);
        $this->SendDebug(__FUNCTION__, ' => errno=' . $cerrno . ', httpcode=' . $httpcode . ', duration=' . $duration . 's', 0);
        $this->SendDebug(__FUNCTION__, '    cdata=' . $cdata, 0);

        $statuscode = 0;
        $err = '';
        $jdata = false;
        if ($cerrno) {
            $statuscode = self::$IS_SERVERERROR;
            $err = 'got curl-errno ' . $cerrno . ' (' . $cerror . ')';
        } elseif ($httpcode != 200) {
            if ($httpcode >= 500 && $httpcode <= 599) {
                $statuscode = self::$IS_SERVERERROR;
                $err = "got http-code $httpcode (server error)";
            } else {
                $err = 'got http-code ' . $httpcode . '(' . $this->HttpCode2Text($httpcode) . ')';
                $statuscode = self::$IS_HTTPERROR;
            }
        } elseif ($cdata == '') {
            $statuscode = self::$IS_INVALIDDATA;
            $err = 'no data';
        } else {
            $xml = simplexml_load_string($cdata);
            if (gettype($xml) == 'object') {
                $jdata = [];
                foreach ($xml->children() as $key => $val) {
                    $jdata[$key] = (string) $val;
                }
                /*
                $responseID = $this->GetArrayElem($jdata, 'responseID', 0);
                if ($responseID == self::$WC_RES_ERROR_MESSAGE) {
                    $this->SendDebug(__FUNCTION__, 'responseID=' . $responseID . '(' . $this->WebControl_DecodeCode($responseID) . '), jdata=' . print_r($jdata, true), 0);
                    $statuscode = self::$IS_APPFAIL;
                    $err = $this->WebControl_DecodeError($jdata['errorcode']);
                }
                 */
            } else {
                $statuscode = self::$IS_INVALIDDATA;
                $err = 'malformed data';
            }
        }

        if ($statuscode) {
            $this->LogMessage('url=' . $url . ' => statuscode=' . $statuscode . ', err=' . $err, KL_WARNING);
            $this->SendDebug(__FUNCTION__, ' => statuscode=' . $statuscode . ', err=' . $err, 0);
            $this->MaintainStatus($statuscode);
        } else {
            $module_disable = $this->ReadPropertyBoolean('module_disable');
            $this->MaintainStatus($module_disable ? IS_INACTIVE : IS_ACTIVE);
        }

        return $jdata;
    }

    private function WebControl_GetLanguage()
    {
        $payload = [
            self::$WC_TEL_SPRACHE,
            0xff,
        ];
        $jdata = $this->WebControl_HttpRequest($payload);
        $this->SendDebug(__FUNCTION__, 'jdata=' . print_r($jdata, true), 0);
        return isset($jdata['sprache']) ? $jdata['sprache'] : -1;
    }

    private function WebControl_GetDevices()
    {
        $devices = [];

        for ($room_id = 0; $room_id < self::$WC_DEF_MAXRAUM; $room_id++) {
            $payload = [
                self::$WC_TEL_RAUM_ABFRAGEN,
                $room_id,
            ];
            $response = $this->WebControl_HttpRequest($payload);
            $this->SendDebug(__FUNCTION__, 'response=' . print_r($response, true), 0);
            if ($response == false) {
                break;
            }
            $responseID = $this->GetArrayElem($response, 'responseID', 0);
            $this->SendDebug(__FUNCTION__, 'responseID=' . $responseID . '(' . $this->WebControl_DecodeCode($responseID) . ')', 0);
            if ($responseID != self::$WC_RES_RAUM_ABFRAGEN) {
                break;
            }
            $room_name = $this->GetArrayElem($response, 'raumname', '');
            if ($room_name == '') {
                continue;
            }
            for ($channel_id = 0; $channel_id < self::$WC_DEF_MAXKANAL; $channel_id++) {
                $payload = [
                    self::$WC_TEL_KANAL_ABFRAGEN,
                    $room_id,
                    $channel_id,
                ];
                $response = $this->WebControl_HttpRequest($payload);
                $this->SendDebug(__FUNCTION__, 'response=' . print_r($response, true), 0);
                if ($response == false) {
                    break;
                }
                $responseID = $this->GetArrayElem($response, 'responseID', 0);
                $this->SendDebug(__FUNCTION__, 'responseID=' . $responseID . '(' . $this->WebControl_DecodeCode($responseID) . ')', 0);
                if ($responseID != self::$WC_RES_KANAL_ABFRAGEN) {
                    break;
                }
                $channel_name = $this->GetArrayElem($response, 'kanalname', '');
                if ($channel_name == '') {
                    continue;
                }

                $produkttyp = $this->GetArrayElem($response, 'produkttyp', 255);
                switch ($produkttyp) {
                    case 3: // Markise
                    case 5: // Markise mit Windsensor
                        $product = self::$PRODUCT_AWNING;
                        break;
                    default:
                        $this->SendDebug(__FUNCTION__, 'unknown produkttyp ' . $produkttyp, 0);
                        $product = self::$PRODUCT_UNKNOWN;
                        break;
                }

                $device = [
                    'room_id'      => $room_id,
                    'room_name'    => $room_name,
                    'channel_id'   => $channel_id,
                    'channel_name' => $channel_name,
                    'interface'    => self::$INTERFACE_WEBCONTROL,
                    'product'      => $product,
                    'actions'      => '',
                ];
                $devices[] = $device;
                $this->SendDebug(__FUNCTION__, 'device=' . print_r($device, true), 0);
            }
        }
        $ret = [
            'State'  => self::$STATE_OK,
            'Data'   => $devices,
        ];
        return $ret;
    }

    private function WebControl_DoPolling($room_id, $channel_id, $poll_cmd, $max_rep)
    {
        $payload = [
            self::$WC_TEL_POLLING,
            $room_id,
            $channel_id,
            $poll_cmd,
        ];
        for ($i = 0; $i < $max_rep; $i++) {
            IPS_Sleep(500);
            $response = $this->WebControl_HttpRequest($payload);
            $this->SendDebug(__FUNCTION__, 'repeat ' . $i . ', response=' . print_r($response, true), 0);
            if ($response == false) {
                break;
            }
            $responseID = $this->GetArrayElem($response, 'responseID', 0);
            $this->SendDebug(__FUNCTION__, 'repeat ' . $i . ', responseID=' . $responseID . '(' . $this->WebControl_DecodeCode($responseID) . ')', 0);
            if (isset($response['befehl'])) {
                $befehl = $response['befehl'];
                $this->SendDebug(__FUNCTION__, 'repeat ' . $i . ', befehl=' . $befehl . '(' . $this->WebControl_DecodeBefehl($befehl) . ')', 0);
                if ($befehl != $poll_cmd) {
                    break;
                }
            } else {
                break;
            }
        }
        return $response;
    }

    private function WebControl_QueryDeviceStatus($jdata)
    {
        foreach (['room_id', 'channel_id'] as $v) {
            if (isset($jdata[$v]) == false) {
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => 'missing ' . $v,
                ];
                return $ret;
            }
            ${$v} = $jdata[$v];
        }

        $payload = [
            self::$WC_TEL_POS_RUECKMELDUNG,
            $room_id,
            $channel_id,
        ];
        $response = $this->WebControl_HttpRequest($payload);
        $this->SendDebug(__FUNCTION__, 'response=' . print_r($response, true), 0);
        if ($response == false) {
            $ret = [
                'State'  => self::$STATE_ERROR,
                'Error'  => 'URGS',
            ];
            $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
            return $ret;
        }

        $responseID = $this->GetArrayElem($response, 'responseID', 0);
        $this->SendDebug(__FUNCTION__, 'responseID=' . $responseID . '(' . $this->WebControl_DecodeCode($responseID) . ')', 0);
        $response = $this->WebControl_DoPolling($room_id, $channel_id, self::$WC_POLL_POSITION, 20);
        if ($response == false) {
            $ret = [
                'State'  => self::$STATE_ERROR,
                'Error'  => 'URGS',
            ];
            $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
            return $ret;
        }

        $responseID = $this->GetArrayElem($response, 'responseID', 0);
        switch ($responseID) {
            case self::$WC_RES_POS_RUECKMELDUNG:
                $r = [];
                if (isset($response['fahrt'])) {
                    $r['fahrt'] = boolval($response['fahrt']);
                }
                if (isset($response['position'])) {
                    $i = intval($response['position']);
                    if ($i != 255) {
                        if ($i % 2) {
                            $i++;
                        }
                        $r['position'] = (int) ($i / 2);
                    } else {
                        $r['position'] = 0;
                    }
                }
                if (isset($response['winkel'])) {
                    $i = intval($response['winkel']);
                    if ($i != 255) {
                        $r['winkel'] = $i - 127;
                    }
                }
                if (isset($response['positionvolant1'])) {
                    $i = intval($response['positionvolant1']);
                    if ($i != 255) {
                        if ($i % 2) {
                            $i++;
                        }
                        $r['positionvolant1'] = (int) ($i / 2);
                    }
                }
                if (isset($response['positionvolant2'])) {
                    $i = intval($response['positionvolant2']);
                    if ($i != 255) {
                        if ($i % 2) {
                            $i++;
                        }
                        $r['positionvolant2'] = (int) ($i / 2);
                    }
                }
                $ret = [
                    'State'  => self::$STATE_OK,
                    'Data'   => $r,
                ];
                break;
            case self::$WC_RES_POLLING:
                $feedback = $this->GetArrayElem($response, 'feedback', 0);
                if ($feedback > 0) {
                    $ret = [
                        'State'  => self::$STATE_CHANNEL_UNREACHABLE,
                        'Error'  => 'feedback=' . $feedback,
                    ];
                } else {
                    $ret = [
                        'State'  => self::$STATE_OK,
                    ];
                }
                break;
            case self::$WC_RES_ERROR_MESSAGE:
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => $this->WebControl_DecodeError($response['errorcode']),
                ];
                break;
            default:
                $ret = [
                    'State'  => self::$STATE_GATEWAY_UNREACHABLE,
                    'Error'  => 'communication failed',
                ];
                break;
        }
        $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
        return $ret;
    }

    private function WebControl_Kanalbedienung($key, $jdata)
    {
        foreach (['room_id', 'channel_id'] as $v) {
            if (isset($jdata[$v]) == false) {
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => 'missing ' . $v,
                ];
                return $ret;
            }
            ${$v} = $jdata[$v];
        }
        for ($i = 1; $i <= 4; $i++) {
            $s = 'arg' . $i;
            $arg[$i] = isset($jdata[$s]) ? $jdata[$s] : 0;
        }

        $payload = [
            self::$WC_TEL_KANALBEDIENUNG,
            $room_id,
            $channel_id,
            $key,
            $arg[1],
            $arg[2],
            $arg[3],
            $arg[4],
        ];
        $response = $this->WebControl_HttpRequest($payload);
        $this->SendDebug(__FUNCTION__, 'response=' . print_r($response, true), 0);
        if ($response == false) {
            $ret = [
                'State'  => self::$STATE_ERROR,
                'Error'  => 'URGS',
            ];
            $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
            return $ret;
        }

        $responseID = $this->GetArrayElem($response, 'responseID', 0);
        $this->SendDebug(__FUNCTION__, 'responseID=' . $responseID . '(' . $this->WebControl_DecodeCode($responseID) . ')', 0);
        $response = $this->WebControl_DoPolling($room_id, $channel_id, self::$WC_POLL_KANALBEDIENUNG, 2);
        if ($response == false) {
            $ret = [
                'State'  => self::$STATE_ERROR,
                'Error'  => 'URGS',
            ];
            $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
            return $ret;
        }

        $responseID = $this->GetArrayElem($response, 'responseID', 0);
        switch ($responseID) {
            case self::$WC_RES_KANALBEDIENUNG:
                $feedback = $this->GetArrayElem($response, 'feedback', 0);
                $r = [
                    'processing' => $feedback == 0,
                    'sun'        => $feedback & 1,
                    'wind'       => $feedback & 2,
                    'rain'       => $feedback & 4,
                    'twilight'   => $feedback & 8,
                ];
                $ret = [
                    'State'  => self::$STATE_OK,
                    'Data'   => $r,
                ];
                break;
            case self::$WC_RES_POLLING:
                $feedback = $this->GetArrayElem($response, 'feedback', 0);
                if ($feedback > 0) {
                    $ret = [
                        'State'  => self::$STATE_CHANNEL_UNREACHABLE,
                        'Error'  => 'feedback=' . $feedback,
                    ];
                } else {
                    $ret = [
                        'State'  => self::$STATE_OK,
                    ];
                }
                break;
            case self::$WC_RES_ERROR_MESSAGE:
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => $this->WebControl_DecodeError($response['errorcode']),
                ];
                break;
            default:
                $ret = [
                    'State'  => self::$STATE_GATEWAY_UNREACHABLE,
                    'Error'  => 'communication failed',
                ];
                break;
        }
        $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
        return $ret;
    }

    private function WebControl_SendPosition($jdata)
    {
        foreach (['room_id', 'channel_id', 'position'] as $v) {
            if (isset($jdata[$v]) == false) {
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => 'missing ' . $v,
                ];
                return $ret;
            }
            ${$v} = $jdata[$v];
        }
        foreach (['position', 'winkel', 'volant1', 'volant2'] as $v) {
            ${$v} = isset($jdata[$v]) ? $jdata[$v] : 255;
        }

        if ($position != 255) {
            $position *= 2;
        }
        $jdata['arg1'] = $position;
        if ($winkel != 255) {
            $winkel += 127;
        }
        $jdata['arg2'] = $winkel;
        if ($volant1 != 255) {
            $volant1 *= 2;
        }
        $jdata['arg3'] = $volant1;
        if ($volant2 != 255) {
            $volant2 *= 2;
        }
        $jdata['arg4'] = $volant2;

        return $this->WebControl_Kanalbedienung(self::$WC_FC_SOLL_SICHER, $jdata);
    }

    private function WebControl_DecodeError($err)
    {
        $err2text = [
            self::$WC_ERROR_CODE_SD_KARTE        => 'SD_KARTE',
            self::$WC_ERROR_CODE_MAX_SZENEN      => 'MAX_SZENEN',
            self::$WC_ERROR_CODE_MAX_KANAL       => 'MAX_KANAL',
            self::$WC_ERROR_CODE_POLLING_BEFEHL  => 'POLLING_BEFEHL',
            self::$WC_ERROR_CODE_POLLING_KANAL   => 'POLLING_KANAL',
            self::$WC_ERROR_CODE_PROJECT_FILE    => 'PROJECT_FILE',
            self::$WC_ERROR_CODE_BEREICHINDEX    => 'BEREICHINDEX',
            self::$WC_ERROR_CODE_CONTENT_INVALID => 'CONTENT_INVALID',
            self::$WC_ERROR_CODE_PANID_INVALID   => 'PANID_INVALID',
        ];

        if (isset($err2text[$err])) {
            $s = $err2text[$err];
        } else {
            $s = 'unknown error ' . $err;
        }
        return $s;
    }

    private function WebControl_DecodeCode($code)
    {
        $code2text = [
            self::$WC_TEL_RAUM_ANLEGEN                  => 'TEL_RAUM_ANLEGEN',
            self::$WC_RES_RAUM_ANLEGEN                  => 'RES_RAUM_ANLEGEN',
            self::$WC_TEL_RAUM_ABFRAGEN                 => 'TEL_RAUM_ABFRAGEN',
            self::$WC_RES_RAUM_ABFRAGEN                 => 'RES_RAUM_ABFRAGEN',
            self::$WC_TEL_RAUMNAMEN_AENDERN             => 'TEL_RAUMNAMEN_AENDERN',
            self::$WC_RES_RAUMNAMEN_AENDERN             => 'RES_RAUMNAMEN_AENDERN',
            self::$WC_TEL_RAUMREIHENFOLGE_AENDERN       => 'TEL_RAUMREIHENFOLGE_AENDERN',
            self::$WC_RES_RAUMREIHENFOLGE_AENDERN       => 'RES_RAUMREIHENFOLGE_AENDERN',
            self::$WC_TEL_RAUM_LOESCHEN                 => 'TEL_RAUM_LOESCHEN',
            self::$WC_RES_RAUM_LOESCHEN                 => 'RES_RAUM_LOESCHEN',
            self::$WC_TEL_KANAL_ANLEGEN                 => 'TEL_KANAL_ANLEGEN',
            self::$WC_RES_KANAL_ANLEGEN                 => 'RES_KANAL_ANLEGEN',
            self::$WC_TEL_KANAL_ABFRAGEN                => 'TEL_KANAL_ABFRAGEN',
            self::$WC_RES_KANAL_ABFRAGEN                => 'RES_KANAL_ABFRAGEN',
            self::$WC_TEL_KANALNAMEN_AENDERN            => 'TEL_KANALNAMEN_AENDERN',
            self::$WC_RES_KANALNAMEN_AENDERN            => 'RES_KANALNAMEN_AENDERN',
            self::$WC_TEL_KANALREIHENFOLGE_AENDERN      => 'TEL_KANALREIHENFOLGE_AENDERN',
            self::$WC_RES_KANALREIHENFOLGE_AENDERN      => 'RES_KANALREIHENFOLGE_AENDERN',
            self::$WC_TEL_KANAL_LOESCHEN                => 'TEL_KANAL_LOESCHEN',
            self::$WC_RES_KANAL_LOESCHEN                => 'RES_KANAL_LOESCHEN',
            self::$WC_TEL_RAUM_KOPIEREN                 => 'TEL_RAUM_KOPIEREN',
            self::$WC_RES_RAUM_KOPIEREN                 => 'RES_RAUM_KOPIEREN',
            self::$WC_TEL_KANAL_IN_RAUM_KOPIEREN        => 'TEL_KANAL_IN_RAUM_KOPIEREN',
            self::$WC_RES_KANAL_IN_RAUM_KOPIEREN        => 'RES_KANAL_IN_RAUM_KOPIEREN',
            self::$WC_TEL_AKTOREN_ZUWEISEN              => 'TEL_AKTOREN_ZUWEISEN',
            self::$WC_RES_AKTOREN_ZUWEISEN              => 'RES_AKTOREN_ZUWEISEN',
            self::$WC_TEL_INFRASTRUKTUR_SPEICHERN       => 'TEL_INFRASTRUKTUR_SPEICHERN',
            self::$WC_RES_INFRASTRUKTUR_SPEICHERN       => 'RES_INFRASTRUKTUR_SPEICHERN',
            self::$WC_TEL_INFRASTRUKTUR_LADEN           => 'TEL_INFRASTRUKTUR_LADEN',
            self::$WC_RES_INFRASTRUKTUR_LADEN           => 'RES_INFRASTRUKTUR_LADEN',
            self::$WC_TEL_DEF_INFRASTRUKTUR_SPEICHERN   => 'TEL_DEF_INFRASTRUKTUR_SPEICHERN',
            self::$WC_RES_DEF_INFRASTRUKTUR_SPEICHERN   => 'RES_DEF_INFRASTRUKTUR_SPEICHERN',
            self::$WC_TEL_KANALBEDIENUNG                => 'TEL_KANALBEDIENUNG',
            self::$WC_RES_KANALBEDIENUNG                => 'RES_KANALBEDIENUNG',
            self::$WC_TEL_POS_RUECKMELDUNG              => 'TEL_POS_RUECKMELDUNG',
            self::$WC_RES_POS_RUECKMELDUNG              => 'RES_POS_RUECKMELDUNG',
            self::$WC_TEL_WINKEN                        => 'TEL_WINKEN',
            self::$WC_RES_WINKEN                        => 'RES_WINKEN',
            self::$WC_TEL_PASSWORT_ABFRAGE              => 'TEL_PASSWORT_ABFRAGE',
            self::$WC_RES_PASSWORT_ABFRAGE              => 'RES_PASSWORT_ABFRAGE',
            self::$WC_TEL_PASSWORT_AENDERN              => 'TEL_PASSWORT_AENDERN',
            self::$WC_RES_PASSWORT_AENDERN              => 'RES_PASSWORT_AENDERN',
            self::$WC_TEL_AUTOMATIK                     => 'TEL_AUTOMATIK',
            self::$WC_RES_AUTOMATIK                     => 'RES_AUTOMATIK',
            self::$WC_TEL_GRENZWERTE                    => 'TEL_GRENZWERTE',
            self::$WC_RES_GRENZWERTE                    => 'RES_GRENZWERTE',
            self::$WC_TEL_RTC                           => 'TEL_RTC',
            self::$WC_RES_RTC                           => 'RES_RTC',
            self::$WC_TEL_POLLING                       => 'TEL_POLLING',
            self::$WC_RES_POLLING                       => 'RES_POLLING',
            self::$WC_RES_WMS_STACK_BUSY                => 'RES_WMS_STACK_BUSY',
            self::$WC_RES_ERROR_MESSAGE                 => 'RES_ERROR_MESSAGE',
            self::$WC_TEL_SPRACHE                       => 'TEL_SPRACHE',
            self::$WC_RES_SPRACHE                       => 'RES_SPRACHE',
            self::$WC_TEL_SET_GRENZWERTE                => 'TEL_SET_GRENZWERTE',
            self::$WC_RES_SET_GRENZWERTE                => 'RES_SET_GRENZWERTE',
            self::$WC_TEL_SZENE_ANLEGEN                 => 'TEL_SZENE_ANLEGEN',
            self::$WC_RES_SZENE_ANLEGEN                 => 'RES_SZENE_ANLEGEN',
            self::$WC_TEL_KANAL_SZENE_ABFRAGEN          => 'TEL_KANAL_SZENE_ABFRAGEN',
            self::$WC_RES_KANAL_SZENE_ABFRAGEN          => 'RES_KANAL_SZENE_ABFRAGEN',
            self::$WC_TEL_LESE_MENUETABELLE_FIX         => 'TEL_LESE_MENUETABELLE_FIX',
            self::$WC_RES_LESE_MENUETABELLE_FIX         => 'RES_LESE_MENUETABELLE_FIX',
            self::$WC_TEL_LESE_MENUETABELLE_PRODUKT_ABH => 'TEL_LESE_MENUETABELLE_PRODUKT_ABH',
            self::$WC_RES_LESE_MENUETABELLE_PRODUKT_ABH => 'RES_LESE_MENUETABELLE_PRODUKT_ABH',
            self::$WC_TEL_LESE_WMS_PARAMETER            => 'TEL_LESE_WMS_PARAMETER',
            self::$WC_RES_LESE_WMS_PARAMETER            => 'RES_LESE_WMS_PARAMETER',
            self::$WC_TEL_SCHREIBE_WMS_PARAMETER        => 'TEL_SCHREIBE_WMS_PARAMETER',
            self::$WC_RES_SCHREIBE_WMS_PARAMETER        => 'RES_SCHREIBE_WMS_PARAMETER',
            self::$WC_TEL_WMS_INDEX_HEADER_1            => 'TEL_WMS_INDEX_HEADER_1',
            self::$WC_RES_WMS_INDEX_HEADER_1            => 'RES_WMS_INDEX_HEADER_1',
            self::$WC_TEL_WMS_INDEX_HEADER_2            => 'TEL_WMS_INDEX_HEADER_2',
            self::$WC_RES_WMS_INDEX_HEADER_2            => 'RES_WMS_INDEX_HEADER_2',
            self::$WC_TEL_WMS_INDEX_HEADER_3            => 'TEL_WMS_INDEX_HEADER_3',
            self::$WC_RES_WMS_INDEX_HEADER_3            => 'RES_WMS_INDEX_HEADER_3',
            self::$WC_TEL_WMS_INDEX_PARAMETERNAME       => 'TEL_WMS_INDEX_PARAMETERNAME',
            self::$WC_RES_WMS_INDEX_PARAMETERNAME       => 'RES_WMS_INDEX_PARAMETERNAME',
            self::$WC_TEL_WMS_RECHTE                    => 'TEL_WMS_RECHTE',
            self::$WC_RES_WMS_RECHTE                    => 'RES_WMS_RECHTE',
            self::$WC_TEL_WMS_INDEX_PARAMETERTYP        => 'TEL_WMS_INDEX_PARAMETERTYP',
            self::$WC_RES_WMS_INDEX_PARAMETERTYP        => 'RES_WMS_INDEX_PARAMETERTYP',
            self::$WC_TEL_WMS_PARAMETER_DEFAULTWERT     => 'TEL_WMS_PARAMETER_DEFAULTWERT',
            self::$WC_RES_WMS_PARAMETER_DEFAULTWERT     => 'RES_WMS_PARAMETER_DEFAULTWERT',
            self::$WC_TEL_GET_WMS_PARAMETER             => 'TEL_GET_WMS_PARAMETER',
            self::$WC_RES_GET_WMS_PARAMETER             => 'RES_GET_WMS_PARAMETER',
            self::$WC_TEL_SET_WMS_PARAMETER             => 'TEL_SET_WMS_PARAMETER',
            self::$WC_RES_SET_WMS_PARAMETER             => 'RES_SET_WMS_PARAMETER',
            self::$WC_TEL_GET_WMS_PARAMETER_ZSP         => 'TEL_GET_WMS_PARAMETER_ZSP',
            self::$WC_RES_GET_WMS_PARAMETER_ZSP         => 'RES_GET_WMS_PARAMETER_ZSP',
            self::$WC_TEL_SET_WMS_PARAMETER_ZSP         => 'TEL_SET_WMS_PARAMETER_ZSP',
            self::$WC_RES_SET_WMS_PARAMETER_ZSP         => 'RES_SET_WMS_PARAMETER_ZSP',
            self::$WC_TEL_KOMFORT                       => 'TEL_KOMFORT',
            self::$WC_RES_KOMFORT                       => 'RES_KOMFORT',
        ];

        if (isset($code2text[$code])) {
            $s = $code2text[$code];
        } else {
            $s = 'unknown code ' . $code;
        }
        return $s;
    }

    private function WebControl_DecodeBefehl($befehl)
    {
        $befehl2text = [
            self::$WC_POLL_KANALBEDIENUNG     => 'POLL_KANALBEDIENUNG',
            self::$WC_POLL_POSITION           => 'POLL_POSITION',
            self::$WC_POLL_GRENZWERTE         => 'POLL_GRENZWERTE',
            self::$WC_POLL_AKTOREN_ZUWEISEN   => 'POLL_AKTOREN_ZUWEISEN',
            self::$WC_POLL_AUTOMATIK          => 'POLL_AUTOMATIK',
            self::$WC_POLL_WINKEN             => 'POLL_WINKEN',
            self::$WC_POLL_SET_GRENZWERTE     => 'POLL_SET_GRENZWERTE',
            self::$WC_POLL_READ_MENUETAB_FIX  => 'POLL_READ_MENUETAB_FIX',
            self::$WC_POLL_READ_MENUETAB_PROD => 'POLL_READ_MENUETAB_PROD',
            self::$WC_POLL_READ_WMS_PARA      => 'POLL_READ_WMS_PARA',
            self::$WC_POLL_WRITE_WMS_PARA     => 'POLL_WRITE_WMS_PARA',
            self::$WC_POLL_KOMFORT            => 'POLL_KOMFORT',
        ];

        if (isset($befehl2text[$befehl])) {
            $s = $befehl2text[$befehl];
        } else {
            $s = 'unknown befehl ' . $befehl;
        }
        return $s;
    }

    // WebControl Pro

    private function WebControlPro_HttpRequest($headerfields, $payload)
    {
        $host = $this->ReadPropertyString('host');

        $url = 'http://' . $host . '/commonCommand';

        $header = $this->build_header($headerfields);

        $curl_opts = [
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => $header,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HEADER         => true,
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ];

        $this->SendDebug(__FUNCTION__, 'http-GET, url=' . $url, 0);
        $this->SendDebug(__FUNCTION__, '... header=' . print_r($header, true), 0);
        $this->SendDebug(__FUNCTION__, '... payload=' . $payload, 0);

        $time_start = microtime(true);

        $ch = curl_init();
        curl_setopt_array($ch, $curl_opts);
        $response = curl_exec($ch);
        $cerrno = curl_errno($ch);
        $cerror = $cerrno ? curl_error($ch) : '';
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_info = curl_getinfo($ch);
        curl_close($ch);

        $duration = round(microtime(true) - $time_start, 2);
        $this->SendDebug(__FUNCTION__, ' => errno=' . $cerrno . ', httpcode=' . $httpcode . ', duration=' . $duration . 's', 0);

        $statuscode = 0;
        $err = '';
        if ($cerrno) {
            $statuscode = self::$IS_SERVERERROR;
            $err = 'got curl-errno ' . $cerrno . ' (' . $cerror . ')';
        } else {
            $header_size = $curl_info['header_size'];
            $head = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
            $this->SendDebug(__FUNCTION__, ' => head=' . $head, 0);
            $this->SendDebug(__FUNCTION__, ' => body=' . $body, 0);
            if ($httpcode >= 500 && $httpcode <= 599) {
                $statuscode = self::$IS_SERVERERROR;
                $err = 'got http-code ' . $httpcode . ' (server error)';
            } elseif ($httpcode != 200) {
                $statuscode = self::$IS_HTTPERROR;
                $err = 'got http-code ' . $httpcode . '(' . $this->HttpCode2Text($httpcode) . ')';
            }
        }
        if ($statuscode == 0) {
            $jbody = @json_decode($body, true);
            if ($jbody == false) {
                $statuscode = self::$IS_INVALIDDATA;
                $err = 'invalid/malformed data';
            }
        }

        if ($statuscode) {
            $this->SendDebug(__FUNCTION__, ' => statuscode=' . $statuscode . ', err=' . $err, 0);
            $this->MaintainStatus($statuscode);
            return false;
        }

        $this->MaintainStatus(IS_ACTIVE);

        return $jbody;
    }

    private function WebControlPro_GetDevices()
    {
        $payload = [
            'protocolVersion' => '1.0',
            'source'          => 2,
            'command'         => 'getConfiguration',
        ];

        $headerfields = [
            'Content-Type'    => 'application/json',
        ];

        $response = $this->WebControlPro_HttpRequest($headerfields, json_encode($payload));
        $this->SendDebug(__FUNCTION__, 'response=' . print_r($response, true), 0);

        $devices = [];

        if (isset($response['destinations']) && isset($response['rooms'])) {
            $destinations = $response['destinations'];
            $rooms = $response['rooms'];

            foreach ($destinations as $destination) {
                $channel_id = $destination['id'];
                $channel_name = $destination['names'][0];
                $product = $destination['animationType'];
                $actions = $destination['actions'];
                $room_id = 0;
                $room_name = '';
                foreach ($rooms as $room) {
                    foreach ($room['destinations'] as $dest) {
                        if ($dest == $channel_id) {
                            $room_id = $room['id'];
                            $room_name = $room['name'];
                            break;
                        }
                    }
                    if ($room_id != 0) {
                        break;
                    }
                }

                $this->SendDebug(__FUNCTION__, 'channel=' . $channel_id . '(' . $channel_name . '), room=' . $room_id . '(' . $room_name . ')', 0);

                foreach ($actions as $action) {
                    $s = 'action=' . $action['id'];
                    $actionType = $action['actionType'];
                    $s .= ', type=' . $actionType . '(' . $this->DecodeActionType($actionType) . ')';
                    $actionDesc = $action['actionDescription'];
                    $s .= ', desc=' . $actionDesc . '(' . $this->DecodeActionDesc($actionDesc) . ')';
                    if (isset($action['minValue']) && isset($action['maxValue'])) {
                        $minValue = $action['minValue'];
                        $maxValue = $action['maxValue'];
                        $s .= ' [' . $minValue . '..' . $maxValue . ']';
                    }
                    $this->SendDebug(__FUNCTION__, '  ' . $s, 0);
                }

                $device = [
                    'room_id'      => $room_id,
                    'room_name'    => $room_name,
                    'channel_id'   => $channel_id,
                    'channel_name' => $channel_name,
                    'interface'    => self::$INTERFACE_WEBCONTROLPRO,
                    'product'      => $product,
                    'actions'      => json_encode($actions),
                ];
                $devices[] = $device;
                $this->SendDebug(__FUNCTION__, 'device=' . print_r($device, true), 0);
            }
        }
        $ret = [
            'State'  => self::$STATE_OK,
            'Data'   => $devices,
        ];
        return $ret;
    }

    private function WebControlPro_QueryDeviceStatus($jdata)
    {
        foreach (['channel_id'] as $v) {
            if (isset($jdata[$v]) == false) {
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => 'missing ' . $v,
                ];
                return $ret;
            }
            ${$v} = $jdata[$v];
        }

        $payload = [
            'protocolVersion' => '1.0',
            'source'          => 2,
            'command'         => 'getStatus',
            'destinations'    => [$channel_id],
        ];

        $headerfields = [
            'Content-Type'    => 'text/plain',
        ];

        $i = 0;
        while (true) {
            $response = $this->WebControlPro_HttpRequest($headerfields, json_encode($payload, JSON_PRETTY_PRINT));
            $this->SendDebug(__FUNCTION__, 'response=' . print_r($response, true), 0);

            if (isset($response['details'][0])) {
                $ret = [
                    'State'  => self::$STATE_OK,
                    'Data'   => $response['details'][0],
                ];
            } elseif (isset($response['errors'])) {
                if (in_array(327684, $response['errors'])) {
                    $state = self::$STATE_CHANNEL_UNREACHABLE;
                } else {
                    $state = self::$STATE_ERROR;
                }
                $ret = [
                    'State'  => $state,
                    'Error'  => implode(',', $response['errors']),
                ];
            } else {
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => 'malformed data',
                ];
            }

            if ($ret['State'] != self::$STATE_CHANNEL_UNREACHABLE) {
                break;
            }
            if ($i++ == self::$WCP_RETRY_COUNT) {
                break;
            }
            $this->SendDebug(__FUNCTION__, 'channel unreachable, sleep ' . self::$WCP_RETRY_DELAY . ' until retry #' . $i, 0);
            IPS_Sleep(self::$WCP_RETRY_DELAY);
        }

        return $ret;
    }

    private function WebControlPro_SendAction($jdata)
    {
        foreach (['actions'] as $v) {
            if (isset($jdata[$v]) == false) {
                $ret = [
                    'State'  => self::$STATE_ERROR,
                    'Error'  => 'missing ' . $v,
                ];
                return $ret;
            }
            ${$v} = $jdata[$v];
        }

        $payload = [
            'protocolVersion' => '1.0',
            'source'          => 2,
            'command'         => 'action',
            'responseType'    => 0,
            'actions'         => $actions,
        ];

        $headerfields = [
            'Content-Type'    => 'text/plain',
        ];

        $response = $this->WebControlPro_HttpRequest($headerfields, json_encode($payload, JSON_PRETTY_PRINT));
        $this->SendDebug(__FUNCTION__, 'response=' . print_r($response, true), 0);
        if (isset($response['errors'])) {
            $ret = [
                'State'  => self::$STATE_ERROR,
                'Error'  => implode(',', $response['errors']),
            ];
        } else {
            $ret = [
                'State'  => self::$STATE_OK,
                'Data'   => '',
            ];
        }
        return $ret;
    }

    private function WebControlPro_SendPing()
    {
        $payload = [
            'protocolVersion' => '1.0',
            'source'          => 2,
            'command'         => 'ping',
        ];

        $headerfields = [
            'Content-Type'    => 'text/plain',
        ];

        $response = $this->WebControlPro_HttpRequest($headerfields, json_encode($payload, JSON_PRETTY_PRINT));
        $this->SendDebug(__FUNCTION__, 'response=' . print_r($response, true), 0);
        if (isset($response['errors'])) {
            $ret = [
                'State'  => self::$STATE_ERROR,
                'Error'  => implode(',', $response['errors']),
            ];
        } elseif (isset($response['status']) && $response['status'] != 0) {
            $ret = [
                'State'  => self::$STATE_GATEWAY_UNREACHABLE,
                'Error'  => implode(',', $response['errors']),
            ];
        } else {
            $ret = [
                'State'  => self::$STATE_OK,
                'Data'   => '',
            ];
        }
        return $ret;
    }
}
