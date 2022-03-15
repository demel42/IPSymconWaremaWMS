<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/CommonStubs/common.php'; // globale Funktionen
require_once __DIR__ . '/../libs/local.php';  // lokale Funktionen

class WaremaWebControlIO extends IPSModule
{
    use StubsCommonLib;
    use WaremaWebControlLocalLib;

    private static $TEL_RAUM_ABFRAGEN = 3;
    private static $TEL_KANAL_ABFRAGEN = 13;
    private static $TEL_KANALBEDIENUNG = 33;
    private static $TEL_POS_RUECKMELDUNG = 35;
    private static $TEL_AUTOMATIK = 43;
    private static $TEL_POLLING = 49;
    private static $TEL_SPRACHE = 61;
    private static $TEL_LESE_WMS_PARAMETER = 77;

    private static $POLL_POSITION = 1;

    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyBoolean('module_disable', false);

        $this->RegisterPropertyString('host', '');

        $this->RegisterAttributeInteger('command_counter', 1);

        $this->InstallVarProfiles(false);
    }

    private function CheckConfiguration()
    {
        $s = '';
        $r = [];

        $host = $this->ReadPropertyString('host');
        if ($host == '') {
            $this->SendDebug(__FUNCTION__, '"host" is needed', 0);
            $r[] = $this->Translate('Host must be specified');
        }

        if ($r != []) {
            $s = $this->Translate('The following points of the configuration are incorrect') . ':' . PHP_EOL;
            foreach ($r as $p) {
                $s .= '- ' . $p . PHP_EOL;
            }
        }

        return $s;
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $vpos = 0;

        $refs = $this->GetReferenceList();
        foreach ($refs as $ref) {
            $this->UnregisterReference($ref);
        }
        $propertyNames = [];
        foreach ($propertyNames as $name) {
            $oid = $this->ReadPropertyInteger($name);
            if ($oid >= 10000) {
                $this->RegisterReference($oid);
            }
        }

        $module_disable = $this->ReadPropertyBoolean('module_disable');
        if ($module_disable) {
            $this->SetStatus(IS_INACTIVE);
            return;
        }

        if ($this->CheckConfiguration() != false) {
            $this->SetStatus(self::$IS_INVALIDCONFIG);
            return;
        }

        $this->SetStatus(IS_ACTIVE);
    }

    protected function GetFormElements()
    {
        $formElements = [];

        $formElements[] = [
            'type'    => 'Label',
            'caption' => 'Warema WebControl IO'
        ];

        @$s = $this->CheckConfiguration();
        if ($s != '') {
            $formElements[] = [
                'type'    => 'Label',
                'caption' => $s
            ];
            $formElements[] = [
                'type'    => 'Label',
            ];
        }

        $formElements[] = [
            'type'    => 'CheckBox',
            'name'    => 'module_disable',
            'caption' => 'Disable instance'
        ];

        $formElements[] = [
            'type'     => 'ValidationTextBox',
            'name'     => 'host',
            'caption'  => 'Hostname of WebControl',
        ];

        return $formElements;
    }

    protected function GetFormActions()
    {
        $formActions = [];

        $formActions[] = [
            'type'    => 'Button',
            'caption' => 'Test access',
            'onClick' => 'WMS_TestAccess($id);'
        ];

        $formActions[] = [
            'type'      => 'ExpansionPanel',
            'caption'   => 'Expert area',
            'expanded ' => false,
            'items'     => [
                [
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
                            'onClick' => 'WMS_DecodeProtocol($id, $protocol);'
                        ],
                        [
                            'type'    => 'Label',
                        ],
                    ],
                ],
                [
                    'type'    => 'Button',
                    'caption' => 'Get position',
                    'onClick' => 'WMS_GetPosition($id, 0, 0);'
                ],
                [
                    'type'    => 'Button',
                    'caption' => 'Re-install variable-profiles',
                    'onClick' => 'WMS_InstallVarProfiles($id, true);'
                ],
            ],
        ];

        $formActions[] = [
            'type'      => 'ExpansionPanel',
            'caption'   => 'Test area',
            'expanded ' => false,
            'items'     => [
                [
                    'type'    => 'TestCenter',
                ],
            ]
        ];

        $formActions[] = $this->GetInformationForm();
        $formActions[] = $this->GetReferencesForm();

        return $formActions;
    }

    public function RequestAction($Ident, $Value)
    {
        if ($this->CommonRequestAction($Ident, $Value)) {
            return;
        }

        if ($this->GetStatus() == IS_INACTIVE) {
            $this->SendDebug(__FUNCTION__, 'instance is inactive, skip', 0);
            return;
        }

        switch ($Ident) {
            default:
                $this->SendDebug(__FUNCTION__, 'invalid ident ' . $Ident, 0);
                break;
        }
    }

    public function GetPosition($room_id, $channel_id)
    {
        $this->get_Position($room_id, $channel_id);
    }

    public function DecodeProtocol($protocol)
    {
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

        echo $msg;
    }

    private function buildCommand($payload)
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

    private function do_HttpRequest($payload)
    {
        $host = $this->ReadPropertyString('host');

        $cmd = $this->buildCommand($payload);
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
            } else {
                $statuscode = self::$IS_INVALIDDATA;
                $err = 'malformed data';
            }
        }

        if ($statuscode) {
            $this->LogMessage('url=' . $url . ' => statuscode=' . $statuscode . ', err=' . $err, KL_WARNING);
            $this->SendDebug(__FUNCTION__, ' => statuscode=' . $statuscode . ', err=' . $err, 0);
            $this->SetStatus($statuscode);
        } else {
            $module_disable = $this->ReadPropertyBoolean('module_disable');
            $this->SetStatus($module_disable ? IS_INACTIVE : IS_ACTIVE);
        }

        return $jdata;
    }

    public function TestAccess()
    {
        $s = '- ' . $this->Translate('Warema WebControl configuration') . ' -' . PHP_EOL;
        $s .= PHP_EOL;

        $lang = $this->get_Lang();
        if ($lang != -1) {
            $s .= $this->Translate('Language') . ': ' . $this->DecodeLang($lang) . PHP_EOL;

            $s .= $this->Translate('Devices') . ': ' . PHP_EOL;
            $devices = $this->get_Devices();
            if (count($devices)) {
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
        echo $s;
    }

    private function get_Lang()
    {
        $payload = [
            self::$TEL_SPRACHE,
            0xff,
        ];
        $jdata = $this->do_HttpRequest($payload);
        $this->SendDebug(__FUNCTION__, 'jdata=' . print_r($jdata, true), 0);
        return isset($jdata['sprache']) ? $jdata['sprache'] : -1;
    }

    private function get_Devices()
    {
        $room_id = 0;
        $channel_id = 0;
        $jdata = false;

        $devices = [];
        while (true) {
            $payload = [
                self::$TEL_RAUM_ABFRAGEN,
                $room_id,
            ];
            $jdata = $this->do_HttpRequest($payload);
            $this->SendDebug(__FUNCTION__, 'jdata=' . print_r($jdata, true), 0);
            if ($jdata == false) {
                break;
            }
            $room_name = $this->GetArrayElem($jdata, 'raumname', '');
            if ($room_name == '') {
                break;
            }
            while (true) {
                $payload = [
                    self::$TEL_KANAL_ABFRAGEN,
                    $room_id,
                    $channel_id,
                ];
                $jdata = $this->do_HttpRequest($payload);
                $this->SendDebug(__FUNCTION__, 'jdata=' . print_r($jdata, true), 0);
                if ($jdata == false) {
                    break;
                }
                $channel_name = $this->GetArrayElem($jdata, 'kanalname', '');
                if ($channel_name == '') {
                    break;
                }

                $product = $this->GetArrayElem($jdata, 'produkttyp', 255);
                $device = [
                    'room_id'      => $room_id,
                    'room_name'    => $room_name,
                    'channel_id'   => $channel_id,
                    'channel_name' => $channel_name,
                    'product'      => $product,
                ];
                $devices[] = $device;
                $this->SendDebug(__FUNCTION__, 'device=' . print_r($device, true), 0);
                $channel_id++;
            }
            $room_id++;
        }
        return $devices;
    }

    private function get_Position($room_id, $channel_id)
    {
        $payload = [
            self::$TEL_POS_RUECKMELDUNG,
            $room_id,
            $channel_id,
        ];
        $jdata = $this->do_HttpRequest($payload);
        $this->SendDebug(__FUNCTION__, 'jdata=' . print_r($jdata, true), 0);
        if (isset($jdata['feedback']) && $jdata['feedback'] != 0) {
            $payload = [
                self::$TEL_POLLING,
                $room_id,
                $channel_id,
                self::$POLL_POSITION,
            ];
            $i = 0;
            while (true) {
                IPS_Sleep(100);
                $jdata = $this->do_HttpRequest($payload);
                $this->SendDebug(__FUNCTION__, 'jdata=' . print_r($jdata, true), 0);
                if (isset($jdata['feedback']) == false || $jdata['feedback'] == 0) {
                    break;
                }
                if ($i++ > 100) {
                    break;
                }
            }
        }
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
                case 'DeviceList':
                    $devices = $this->get_Devices();
                    $ret = json_encode($devices);
                    break;
                case 'Position':
                    if (isset($jdata['room_id']) == false || isset($jdata['channel_id']) == false) {
                        $this->SendDebug(__FUNCTION__, 'missing room_id/channel_id', 0);
                        break;
                    }
                    $ret = $this->get_Position($jdata['room_id'], $jdata['channel_id']);
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

    /*
    public function Send(string $Text)
    {
        $this->SendDataToChildren(json_encode(['DataID' => '{B78E405B-23E3-10A5-4B26-F24277883F96}', 'Buffer' => $Text]));
    }
     */
}
