<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/CommonStubs/common.php'; // globale Funktionen
require_once __DIR__ . '/../libs/local.php';  // lokale Funktionen

class WaremaWMSDevice extends IPSModule
{
    use StubsCommonLib;
    use WaremaWMSLocalLib;

    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyBoolean('module_disable', false);

        $this->RegisterPropertyInteger('room_id', 0);
        $this->RegisterPropertyInteger('channel_id', 0);
        $this->RegisterPropertyInteger('product', 0);

        $this->RegisterPropertyInteger('update_interval', 60);

        $this->InstallVarProfiles(false);

        $this->RegisterTimer('UpdateStatus', 0, 'WMS_UpdateStatus(' . $this->InstanceID . ');');

        $this->ConnectParent('{6A9BBD57-8473-682D-4ABF-009AE8584B2B}');
    }

    private function CheckConfiguration()
    {
        $s = '';
        $r = [];

        if ($r != []) {
            $s = $this->Translate('The following points of the configuration are incorrect') . ':' . PHP_EOL;
            foreach ($r as $p) {
                $s .= '- ' . $p . PHP_EOL;
            }
        }

        return $s;
    }

    private function product2options($product)
    {
        $options = [
            'position_slider' => false,
            'control3'        => false, // Up, Stop, Down
            'activity'        => false,
        ];

        switch ($product) {
            case self::$PRODUCT_MARKISE:
            case self::$PRODUCT_MARKISE_INT_WIND:
                $options['position_slider'] = true;
                $options['control3'] = true;
                $options['activity'] = true;
                break;
            default:
                $this->SendDebug(__FUNCTION__, 'unknown product ' . $product, 0);
                break;
        }
        return $options;
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $product = $this->ReadPropertyInteger('product');
        $options = $this->product2options($product);
        $this->SendDebug(__FUNCTION__, 'options=' . print_r($options, true), 0);

        $vpos = 0;
        $this->MaintainVariable('State', $this->Translate('State'), VARIABLETYPE_INTEGER, 'WaremaWMS.State', $vpos++, true);

        $vpos = 10;
        $this->MaintainVariable('Position', $this->Translate('Position'), VARIABLETYPE_INTEGER, 'WaremaWMS.Position', $vpos++, $options['position_slider']);
        if ($options['position_slider']) {
            $this->MaintainAction('Position', true);
        }

        if ($options['control3']) {
            $this->MaintainVariable('Control', $this->Translate('Control'), VARIABLETYPE_INTEGER, 'WaremaWMS.Control3', $vpos++, true);
            $this->MaintainAction('Control', true);
        } else {
            $this->UnregisterVariable('Control');
        }

        $this->MaintainVariable('Activity', $this->Translate('Activity'), VARIABLETYPE_INTEGER, 'WaremaWMS.Activity', $vpos++, $options['activity']);

        $vpos = 100;
        $this->MaintainVariable('LastStatus', $this->Translate('Last status update'), VARIABLETYPE_INTEGER, '~UnixTimestamp', $vpos++, true);

        $this->SetSummary($this->DecodeProduct($product));

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
            'caption' => 'Warema WMS Device'
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
            'type'    => 'ExpansionPanel',
            'items'   => [
                [
                    'type'    => 'NumberSpinner',
                    'name'    => 'room_id',
                    'caption' => 'Room ID'
                ],
                [
                    'type'    => 'NumberSpinner',
                    'name'    => 'channel_id',
                    'caption' => 'Channel ID'
                ],
                [
                    'type'     => 'Select',
                    'options'  => $this->ProductAsOptions(),
                    'name'     => 'product',
                    'caption'  => 'Product'
                ],
            ],

            'caption' => 'Basic configuration (don\'t change)'
        ];

        $formElements[] = [
            'type'    => 'NumberSpinner',
            'minimum' => 0,
            'suffix'  => 'Seconds',
            'name'    => 'update_interval',
            'caption' => 'Update status interval'
        ];

        return $formElements;
    }

    protected function GetFormActions()
    {
        $formActions = [];

        $formActions[] = [
            'type'    => 'Button',
            'caption' => 'Update status',
            'onClick' => 'WMS_UpdateStatus($id);'
        ];

        $formActions[] = [
            'type'      => 'ExpansionPanel',
            'caption'   => 'Expert area',
            'expanded ' => false,
            'items'     => [
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

    public function RequestAction($ident, $value)
    {
        if ($this->CommonRequestAction($ident, $value)) {
            return;
        }

        if ($this->GetStatus() == IS_INACTIVE) {
            $this->SendDebug(__FUNCTION__, 'instance is inactive, skip', 0);
            return;
        }

        $this->SendDebug(__FUNCTION__, 'ident=' . $ident . ', value=' . $value, 0);

        $r = false;
        switch ($ident) {
            case 'Control':
                switch ($value) {
                    case self::$CONTROL_STOP:
                        $r = $this->SendStop();
                        break;
                    case self::$CONTROL_UP:
                        $r = $this->SendUp();
                        break;
                    case self::$CONTROL_DOWN:
                        $r = $this->SendDown();
                        break;
                    default:
                        $this->SendDebug(__FUNCTION__, 'invalid value ' . $value . ' for ident ' . $ident, 0);
                        break;
                }
                break;
            case 'Position':
                $r = $this->SendPosition($value);
                break;
            default:
                $this->SendDebug(__FUNCTION__, 'invalid ident ' . $ident, 0);
                break;
        }
        if ($r) {
            $this->SetValue($ident, $value);
            // $this->SetUpdateInterval(1);
        }
    }

    protected function SetUpdateInterval($sec = null)
    {
        if ($sec == null) {
            $sec = $this->ReadPropertyInteger('update_interval');
        }
        $msec = $sec > 0 ? $sec * 1000 : 0;
        $this->SetTimerInterval('UpdateStatus', $msec);
    }

    public function UpdateStatus()
    {
        if ($this->GetStatus() == IS_INACTIVE) {
            $this->SendDebug(__FUNCTION__, 'instance is inactive, skip', 0);
            return;
        }

        if ($this->HasActiveParent() == false) {
            $this->SendDebug(__FUNCTION__, 'has no active parent', 0);
            $this->LogMessage('has no active parent instance', KL_WARNING);
            return;
        }

        $ret = $this->QueryPosition();
        $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
        $this->SetValue('State', $ret['State']);
        if ($ret['State'] == self::$STATE_OK) {
            if (isset($ret['Data']['position'])) {
                $this->SetValue('Position', $ret['Data']['position']);
            }
            if (isset($ret['Data']['fahrt'])) {
                $this->SetValue('Position', boolval($ret['Data']['fahrt']));
            }
        }
        $this->SetValue('LastStatus', time());

        $this->SetUpdateInterval();
    }

    private function SendDataToIO($func, $data)
    {
        $data['DataID'] = '{A8C43E67-9C5C-8A22-1F46-69EC56138C81}';
        $data['Function'] = $func;
        $this->SendDebug(__FUNCTION__, 'data=' . print_r($data, true), 0);
        $ret = $this->SendDataToParent(json_encode($data));
        $ret = json_decode($ret, true);
        $this->SendDebug(__FUNCTION__, 'ret=' . print_r($ret, true), 0);
        return $ret;
    }

    public function SendStop()
    {
        $room_id = $this->ReadPropertyInteger('room_id');
        $channel_id = $this->ReadPropertyInteger('channel_id');

        $data = [
            'room_id'    => $room_id,
            'channel_id' => $channel_id,
        ];
        $ret = $this->SendDataToIO(__FUNCTION__, $data);
        $this->SetValue('State', $ret['State']);
        return $ret['State'] == self::$STATE_OK;
    }

    public function SendUp()
    {
        $room_id = $this->ReadPropertyInteger('room_id');
        $channel_id = $this->ReadPropertyInteger('channel_id');

        $data = [
            'room_id'    => $room_id,
            'channel_id' => $channel_id,
        ];
        $ret = $this->SendDataToIO(__FUNCTION__, $data);
        $this->SetValue('State', $ret['State']);
        return $ret['State'] == self::$STATE_OK;
    }

    public function SendDown()
    {
        $room_id = $this->ReadPropertyInteger('room_id');
        $channel_id = $this->ReadPropertyInteger('channel_id');

        $data = [
            'room_id'    => $room_id,
            'channel_id' => $channel_id,
        ];
        $ret = $this->SendDataToIO(__FUNCTION__, $data);
        $this->SetValue('State', $ret['State']);
        return $ret['State'] == self::$STATE_OK;
    }

    public function SendPosition(int $position)
    {
        $room_id = $this->ReadPropertyInteger('room_id');
        $channel_id = $this->ReadPropertyInteger('channel_id');

        $data = [
            'room_id'    => $room_id,
            'channel_id' => $channel_id,
            'position'   => $position,
        ];
        $ret = $this->SendDataToIO(__FUNCTION__, $data);
        $this->SetValue('State', $ret['State']);
        return $ret['State'] == self::$STATE_OK;
    }

    public function QueryPosition()
    {
        $room_id = $this->ReadPropertyInteger('room_id');
        $channel_id = $this->ReadPropertyInteger('channel_id');

        $data = [
            'room_id'    => $room_id,
            'channel_id' => $channel_id,
        ];
        $ret = $this->SendDataToIO(__FUNCTION__, $data);
        return $ret;
    }
}
