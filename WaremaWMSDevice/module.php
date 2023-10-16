<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/common.php';
require_once __DIR__ . '/../libs/local.php';

class WaremaWMSDevice extends IPSModule
{
    use WaremaWMS\StubsCommonLib;
    use WaremaWMSLocalLib;

    public function __construct(string $InstanceID)
    {
        parent::__construct($InstanceID);

        $this->CommonContruct(__DIR__);
    }

    public function __destruct()
    {
        $this->CommonDestruct();
    }

    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyBoolean('module_disable', false);

        $this->RegisterPropertyBoolean('log_no_parent', true);

        $this->RegisterPropertyInteger('room_id', 0);
        $this->RegisterPropertyInteger('channel_id', 0);
        $this->RegisterPropertyInteger('product', 0);

        $this->RegisterPropertyInteger('update_interval', 15);

        $this->RegisterAttributeString('UpdateInfo', json_encode([]));
        $this->RegisterAttributeString('ModuleStats', json_encode([]));

        $this->InstallVarProfiles(false);

        $this->ConnectParent('{6A9BBD57-8473-682D-4ABF-009AE8584B2B}');

        $this->RegisterTimer('UpdateStatus', 0, 'IPS_RequestAction(' . $this->InstanceID . ', "UpdateStatus", "");');

        $this->RegisterMessage(0, IPS_KERNELMESSAGE);
    }

    public function MessageSink($tstamp, $senderID, $message, $data)
    {
        parent::MessageSink($tstamp, $senderID, $message, $data);

        if ($message == IPS_KERNELMESSAGE && $data[0] == KR_READY) {
            $this->SetUpdateInterval();
        }
    }

    private function CheckModuleUpdate(array $oldInfo, array $newInfo)
    {
        $r = [];

        if ($this->version2num($oldInfo) < $this->version2num('1.2.2')) {
            $r[] = $this->Translate('Spelling error in variableprofile \'WaremaWMS.State\'');
        }

        if ($this->version2num($oldInfo) < $this->version2num('1.6')) {
            $r[] = $this->Translate('Variable \'Position\' is no longer an action and is replaced by \'TargetPosition\' in this respect');
        }

        return $r;
    }

    private function CompleteModuleUpdate(array $oldInfo, array $newInfo)
    {
        if ($this->version2num($oldInfo) < $this->version2num('1.2.2')) {
            if (IPS_VariableProfileExists('WaremaWMS.State')) {
                IPS_DeleteVariableProfile('WaremaWMS.State');
            }
            $this->InstallVarProfiles(false);
        }

        if ($this->version2num($oldInfo) < $this->version2num('1.6')) {
            @$varID = $this->GetIDForIdent('Position');
            if (@$varID != false) {
                $this->MaintainAction('Position', false);
            }
        }

        return '';
    }

    private function product2options($product)
    {
        $options = [
            'position_slider' => false,
            'control_awning'  => false, // Retract, Stop, Extend
            'control_blind'   => false, // Up, Stop, Down
            'activity'        => false,
        ];

        switch ($product) {
            case self::$PRODUCT_MARKISE:
            case self::$PRODUCT_MARKISE_INT_WIND:
                $options['position_slider'] = true;
                $options['control_awning'] = true;
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

        $this->MaintainReferences();

        if ($this->CheckPrerequisites() != false) {
            $this->MaintainTimer('UpdateStatus', 0);
            $this->MaintainStatus(self::$IS_INVALIDPREREQUISITES);
            return;
        }

        if ($this->CheckUpdate() != false) {
            $this->MaintainTimer('UpdateStatus', 0);
            $this->MaintainStatus(self::$IS_UPDATEUNCOMPLETED);
            return;
        }

        if ($this->CheckConfiguration() != false) {
            $this->MaintainTimer('UpdateStatus', 0);
            $this->MaintainStatus(self::$IS_INVALIDCONFIG);
            return;
        }

        $product = $this->ReadPropertyInteger('product');
        $options = $this->product2options($product);
        $this->SendDebug(__FUNCTION__, 'options=' . print_r($options, true), 0);

        $vpos = 0;
        $this->MaintainVariable('State', $this->Translate('State'), VARIABLETYPE_INTEGER, 'WaremaWMS.State', $vpos++, true);

        $vpos = 10;
        $this->MaintainVariable('Position', $this->Translate('Position'), VARIABLETYPE_INTEGER, 'WaremaWMS.Position', $vpos++, $options['position_slider']);
        $this->MaintainVariable('TargetPosition', $this->Translate('Target position'), VARIABLETYPE_INTEGER, 'WaremaWMS.Position', $vpos++, $options['position_slider']);
        if ($options['position_slider']) {
            $this->MaintainAction('TargetPosition', true);
        }

        if ($options['control_awning']) {
            $this->MaintainVariable('Control', $this->Translate('Control'), VARIABLETYPE_INTEGER, 'WaremaWMS.ControlAwning', $vpos++, true);
            $this->MaintainAction('Control', true);
        } else {
            $this->UnregisterVariable('Control');
        }

        $this->MaintainVariable('Activity', $this->Translate('Activity'), VARIABLETYPE_INTEGER, 'WaremaWMS.Activity', $vpos++, $options['activity']);

        $vpos = 100;
        $this->MaintainVariable('LastStatus', $this->Translate('Last status update'), VARIABLETYPE_INTEGER, '~UnixTimestamp', $vpos++, true);

        $this->SetSummary($this->DecodeProduct($product));

        $module_disable = $this->ReadPropertyBoolean('module_disable');
        if ($module_disable) {
            $this->MaintainTimer('UpdateStatus', 0);
            $this->MaintainStatus(IS_INACTIVE);
            return;
        }

        $this->MaintainStatus(IS_ACTIVE);

        $this->SetUpdateInterval();
    }

    private function GetFormElements()
    {
        $formElements = $this->GetCommonFormElements('Warema WMS Device');

        if ($this->GetStatus() == self::$IS_UPDATEUNCOMPLETED) {
            return $formElements;
        }

        $formElements[] = [
            'type'    => 'CheckBox',
            'name'    => 'module_disable',
            'caption' => 'Disable instance',
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
            'caption' => 'Update status interval',
        ];

        $formElements[] = [
            'type'    => 'CheckBox',
            'name'    => 'log_no_parent',
            'caption' => 'Generate message when the gateway is inactive',
        ];

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
            'caption' => 'Update status',
            'onClick' => 'IPS_RequestAction(' . $this->InstanceID . ', "UpdateStatus", "");',
        ];

        $formActions[] = [
            'type'      => 'ExpansionPanel',
            'caption'   => 'Expert area',
            'expanded'  => false,
            'items'     => [
                $this->GetInstallVarProfilesFormItem(),
            ],
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
            case 'TargetPosition':
                $r = $this->SendPosition($value);
                break;
            case 'UpdateStatus':
                $this->UpdateStatus();
                break;
            default:
                $this->SendDebug(__FUNCTION__, 'invalid ident ' . $ident, 0);
                break;
        }
        if ($r) {
            $this->SetValue($ident, $value);
            $this->SetUpdateInterval(0.5);
        }
    }

    private function SetUpdateInterval($sec = null)
    {
        if ($sec == null) {
            $sec = $this->ReadPropertyInteger('update_interval');
        }
        $msec = $sec > 0 ? $sec * 1000 : 0;
        $this->MaintainTimer('UpdateStatus', (int) $msec);
    }

    private function UpdateStatus()
    {
        if ($this->GetStatus() == IS_INACTIVE) {
            $this->SendDebug(__FUNCTION__, 'instance is inactive, skip', 0);
            return;
        }

        if ($this->HasActiveParent() == false) {
            $this->SendDebug(__FUNCTION__, 'has no active parent/gateway', 0);
            $log_no_parent = $this->ReadPropertyBoolean('log_no_parent');
            if ($log_no_parent) {
                $this->LogMessage($this->Translate('Instance has no active gateway'), KL_WARNING);
            }
            return;
        }

        $ret = $this->QueryPosition();

        $this->SetValue('State', $ret['State']);

        $tmout = null;

        if ($ret['State'] == self::$STATE_OK) {
            if (isset($ret['Data']['position'])) {
                $this->SetValue('Position', $ret['Data']['position']);
            }
            if (isset($ret['Data']['fahrt'])) {
                if (boolval($ret['Data']['fahrt'])) {
                    $activity = self::$ACTIVITY_MOVES;
                    $tmout = 0.25;
                } else {
                    $activity = self::$ACTIVITY_STAND;
                }
                $this->SetValue('Activity', $activity);
            }
            if ($this->GetValue('Activity') == self::$ACTIVITY_STAND) {
                $this->SetValue('TargetPosition', $this->GetValue('TargetPosition'));
            }
        }
        $this->SetValue('LastStatus', time());

        $this->SetUpdateInterval($tmout);
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
        $this->SendDebug(__FUNCTION__, '', 0);

        $room_id = $this->ReadPropertyInteger('room_id');
        $channel_id = $this->ReadPropertyInteger('channel_id');

        $data = [
            'room_id'    => $room_id,
            'channel_id' => $channel_id,
        ];
        $ret = $this->SendDataToIO(__FUNCTION__, $data);
        if (isset($ret['Data']['wind']) && $ret['Data']['wind']) {
            $state = self::$STATE_WIND_ALARM;
        } elseif (isset($ret['Data']['rain']) && $ret['Data']['rain']) {
            $state = self::$STATE_RAIN_ALARM;
        } else {
            $state = $ret['State'];
        }
        $this->SetValue('State', $state);
        return $state == self::$STATE_OK;
    }

    public function SendUp()
    {
        $this->SendDebug(__FUNCTION__, '', 0);

        $room_id = $this->ReadPropertyInteger('room_id');
        $channel_id = $this->ReadPropertyInteger('channel_id');

        $data = [
            'room_id'    => $room_id,
            'channel_id' => $channel_id,
        ];
        $ret = $this->SendDataToIO(__FUNCTION__, $data);
        if (isset($ret['Data']['wind']) && $ret['Data']['wind']) {
            $state = self::$STATE_WIND_ALARM;
        } elseif (isset($ret['Data']['rain']) && $ret['Data']['rain']) {
            $state = self::$STATE_RAIN_ALARM;
        } else {
            $state = $ret['State'];
        }
        $this->SetValue('State', $state);
        return $state == self::$STATE_OK;
    }

    public function SendDown()
    {
        $this->SendDebug(__FUNCTION__, '', 0);

        $room_id = $this->ReadPropertyInteger('room_id');
        $channel_id = $this->ReadPropertyInteger('channel_id');

        $data = [
            'room_id'    => $room_id,
            'channel_id' => $channel_id,
        ];
        $ret = $this->SendDataToIO(__FUNCTION__, $data);
        if (isset($ret['Data']['wind']) && $ret['Data']['wind']) {
            $state = self::$STATE_WIND_ALARM;
        } elseif (isset($ret['Data']['rain']) && $ret['Data']['rain']) {
            $state = self::$STATE_RAIN_ALARM;
        } else {
            $state = $ret['State'];
        }
        $this->SetValue('State', $state);
        return $state == self::$STATE_OK;
    }

    public function SendPosition(int $position)
    {
        $this->SendDebug(__FUNCTION__, 'position=' . $position, 0);

        $room_id = $this->ReadPropertyInteger('room_id');
        $channel_id = $this->ReadPropertyInteger('channel_id');

        $data = [
            'room_id'    => $room_id,
            'channel_id' => $channel_id,
            'position'   => $position,
        ];
        $ret = $this->SendDataToIO(__FUNCTION__, $data);
        if (isset($ret['Data']['wind']) && $ret['Data']['wind']) {
            $state = self::$STATE_WIND_ALARM;
        } elseif (isset($ret['Data']['rain']) && $ret['Data']['rain']) {
            $state = self::$STATE_RAIN_ALARM;
        } else {
            $state = $ret['State'];
        }
        $this->SetValue('State', $state);
        return $state == self::$STATE_OK;
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
