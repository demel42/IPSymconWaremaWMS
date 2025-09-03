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

        $this->RegisterPropertyBoolean('log_no_parent', true);

        $this->RegisterPropertyInteger('room_id', 0);
        $this->RegisterPropertyInteger('channel_id', 0);
        $this->RegisterPropertyInteger('interface', 0);
        $this->RegisterPropertyInteger('product', 0);
        $this->RegisterPropertyString('actions', 0);

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

        if ($this->version2num($oldInfo) < $this->version2num('2.0')) {
            $r[] = $this->Translate('Rename of variableprofile \'WaremaWMS.ControlAwning\'');
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

        if ($this->version2num($oldInfo) < $this->version2num('2.0')) {
            if (IPS_VariableProfileExists('WaremaWMS.ControlAwning')) {
                IPS_DeleteVariableProfile('WaremaWMS.ControlAwning');
            }
            if (IPS_VariableProfileExists('WaremaWMS.ControlBlind')) {
                IPS_DeleteVariableProfile('WaremaWMS.ControlBlind');
            }
            $this->InstallVarProfiles(false);
        }

        return '';
    }

    private function actionType2action($actionType)
    {
        $actions = @json_decode((string) $this->ReadPropertyString('actions'), true);
        foreach ($actions as $action) {
            if ($action['actionType'] == $actionType) {
                return $action;
            }
        }
        return false;
    }

    private function product2options($product)
    {
        $options = [
            'position_slider'         => false,
            'rotation_slider'         => false,
            'brightness_slider'       => false,
            'power_slider'            => false,
            'control_extend_retract'  => false, // Retract, Stop, Extend (int)
            'control_up_down'         => false, // Up, Stop, Down (int)
            'control_open_close'      => false, // Open, Stop, Close (int)
            'control_switch'          => false, // On, Off (bool)
            'activity'                => false,
        ];

        $interface = $this->ReadPropertyInteger('interface');
        if ($interface == self::$INTERFACE_WEBCONTROL) {
            switch ($product) {
                case self::$PRODUCT_AWNING:
                    $options['position_slider'] = true;
                    $options['control_extend_retract'] = true;
                    $options['activity'] = true;
                    break;
                default:
                    $this->SendDebug(__FUNCTION__, 'Unknown product ' . $product, 0);
                    break;
            }
        }
        if ($interface == self::$INTERFACE_WEBCONTROLPRO) {
            switch ($product) {
                case self::$PRODUCT_AWNING:
                    $options['control_extend_retract'] = true;
                    $action_position = $this->actionType2action(self::$ACTION_TYPE_PERCENTAGE);
                    if ($action_position !== false) {
                        $options['position_slider'] = true;
                    }
                    $options['activity'] = true;
                    break;
                case self::$PRODUCT_VENETIAN_BLIND:
                case self::$PRODUCT_SHUTTER_BLIND:
                    $options['control_up_down'] = true;
                    $action_position = $this->actionType2action(self::$ACTION_TYPE_PERCENTAGE);
                    if ($action_position !== false) {
                        $options['position_slider'] = true;
                    }
                    $action_rotation = $this->actionType2action(self::$ACTION_TYPE_ROTATION);
                    if ($action_rotation !== false) {
                        $options['rotation_slider'] = true;
                    }
                    $options['activity'] = true;
                    break;
                case self::$PRODUCT_SLAT_ROOF:
                    $options['control_open_close'] = true;
                    $action_position = $this->actionType2action(self::$ACTION_TYPE_PERCENTAGE);
                    if ($action_position !== false) {
                        $options['position_slider'] = true;
                    }
                    $action_rotation = $this->actionType2action(self::$ACTION_TYPE_ROTATION);
                    if ($action_rotation !== false) {
                        $options['rotation_slider'] = true;
                    }
                    $options['activity'] = true;
                    break;
                case self::$PRODUCT_WINDOW:
                    $options['control_open_close'] = true;
                    $action_position = $this->actionType2action(self::$ACTION_TYPE_PERCENTAGE);
                    if ($action_position !== false) {
                        $options['position_slider'] = true;
                    }
                    $options['activity'] = true;
                    break;
                case self::$PRODUCT_SWITCH:
                    $options['control_switch'] = true;
                    break;
                case self::$PRODUCT_DIMMER:
                    $options['control_switch'] = true;
                    $action_percentage = $this->actionType2action(self::$ACTION_TYPE_PERCENTAGE);
                    if ($action_percentage !== false) {
                        if ($action_percentage['actionDescription'] == self::$ACTION_DESC_LIGHT_DIMMING) {
                            $options['brightness_slider'] = true;
                        }
                        if ($action_percentage['actionDescription'] == self::$ACTION_DESC_LOAD_DIMMING) {
                            $options['power_slider'] = true;
                        }
                    }
                    break;
                default:
                    $this->SendDebug(__FUNCTION__, 'Unknown product ' . $product, 0);
                    break;
            }
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

        $this->MaintainVariable('Rotation', $this->Translate('Rotation'), VARIABLETYPE_INTEGER, 'WaremaWMS.Rotation', $vpos++, $options['rotation_slider']);
        $this->MaintainVariable('TargetRotation', $this->Translate('Target rotation'), VARIABLETYPE_INTEGER, 'WaremaWMS.Rotation', $vpos++, $options['rotation_slider']);
        if ($options['rotation_slider']) {
            $this->MaintainAction('TargetRotation', true);
        }

        $this->MaintainVariable('Brightness', $this->Translate('Brightness'), VARIABLETYPE_INTEGER, 'WaremaWMS.Percentage', $vpos++, $options['brightness_slider']);
        if ($options['brightness_slider']) {
            $this->MaintainAction('Brightness', true);
        }

        $this->MaintainVariable('Power', $this->Translate('Power'), VARIABLETYPE_INTEGER, 'WaremaWMS.Percentage', $vpos++, $options['power_slider']);
        if ($options['power_slider']) {
            $this->MaintainAction('Power', true);
        }

        $has_control = false;
        if ($options['control_extend_retract']) {
            $this->MaintainVariable('Control', $this->Translate('Control'), VARIABLETYPE_INTEGER, 'WaremaWMS.ControlExtendRetract', $vpos++, true);
            $has_control = true;
        }
        if ($options['control_up_down']) {
            $this->MaintainVariable('Control', $this->Translate('Control'), VARIABLETYPE_INTEGER, 'WaremaWMS.ControlUpDown', $vpos++, true);
            $has_control = true;
        }
        if ($options['control_open_close']) {
            $this->MaintainVariable('Control', $this->Translate('Control'), VARIABLETYPE_INTEGER, 'WaremaWMS.ControlOpenClose', $vpos++, true);
            $has_control = true;
        }
        if ($has_control) {
            $this->MaintainAction('Control', true);
        } else {
            $this->UnregisterVariable('Control');
        }

        $this->MaintainVariable('Switch', $this->Translate('Switch'), VARIABLETYPE_BOOLEAN, '~Switch', $vpos++, $options['control_switch']);
        if ($options['control_switch']) {
            $this->MaintainAction('Switch', true);
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
                    'enabled' => false,
                    'name'    => 'room_id',
                    'caption' => 'Room ID'
                ],
                [
                    'type'    => 'NumberSpinner',
                    'enabled' => false,
                    'name'    => 'channel_id',
                    'caption' => 'Channel ID'
                ],
                [
                    'type'     => 'Select',
                    'enabled'  => false,
                    'options'  => $this->InterfaceAsOptions(),
                    'name'     => 'interface',
                    'caption'  => 'WMS interface'
                ],
                [
                    'type'     => 'Select',
                    'enabled'  => false,
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
                [
                    'type'    => 'Button',
                    'caption' => 'Show configuration',
                    'onClick' => 'IPS_RequestAction(' . $this->InstanceID . ', "SHowConfiguration", "");',
                ],
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
            case 'TargetRotation':
                $r = $this->SendRotation($value);
                break;
            case 'Switch':
                $r = $this->SendSwitch((bool) $value);
                break;
            case 'Brightness':
            case 'Power':
                $r = $this->SendDim($value);
                break;
            case 'UpdateStatus':
                $this->UpdateStatus();
                break;
            case 'SHowConfiguration':
                $this->SHowConfiguration();
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

    private function ShowConfiguration()
    {
        $interface = $this->ReadPropertyInteger('interface');
        switch ($interface) {
            case self::$INTERFACE_WEBCONTROL:
                break;
            case self::$INTERFACE_WEBCONTROLPRO:
                $s = '';

                $product = $this->ReadPropertyInteger('product');
                $this->SendDebug(__FUNCTION__, 'product=' . $this->DecodeProduct($product) . '(' . $product . ')', 0);
                $s .= $this->Translate('Product') . ': ' . $this->DecodeProduct($product) . '(' . $product . ')' . PHP_EOL;

                $actions = @json_decode((string) $this->ReadPropertyString('actions'), true);
                $this->SendDebug(__FUNCTION__, 'actions=' . print_r($actions, true), 0);

                $s .= $this->Translate('Actions') . ': ' . PHP_EOL;
                foreach ($actions as $action) {
                    $r = 'id=' . $action['id'];

                    $r .= ', type=' . $this->DecodeActionType($action['actionType']) . '(' . $action['actionType'] . ')';
                    $r .= ', desc=' . $this->DecodeActionDesc($action['actionDescription']) . '(' . $action['actionDescription'] . ')';

                    if (isset($action['minValue'])) {
                        $r .= ', min=' . $action['minValue'];
                    }
                    if (isset($action['maxValue'])) {
                        $r .= ', max=' . $action['maxValue'];
                    }

                    $s .= ' - ' . $r . PHP_EOL;
                }

                $options = $this->product2options($product);
                $this->SendDebug(__FUNCTION__, 'options=' . print_r($options, true), 0);
                $v = [];
                foreach ($options as $key => $val) {
                    if ($val) {
                        $v[] = $key;
                    }
                }
                $s .= $this->Translate('Options') . ': ' . implode(', ', $v) . PHP_EOL;

                $this->PopupMessage($s);
                break;
        }
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

        $tmout = null;

        $interface = $this->ReadPropertyInteger('interface');
        if ($interface == self::$INTERFACE_WEBCONTROL) {
            $ret = $this->QueryDeviceStatus();

            $this->SetValue('State', $ret['State']);

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
                    $this->SetValue('TargetPosition', $this->GetValue('Position'));
                }
            }
        }

        if ($interface == self::$INTERFACE_WEBCONTROLPRO) {
            $product = $this->ReadPropertyInteger('product');
            $options = $this->product2options($product);
            $ret = $this->QueryDeviceStatus();

            $this->SetValue('State', $ret['State']);

            if ($ret['State'] == self::$STATE_OK) {
                if ($options['activity']) {
                    if (isset($ret['Data']['data']['drivingCause'])) {
                        $drivingCause = $ret['Data']['data']['drivingCause'];
                        if ($drivingCause == self::$DRIVING_CLAUSE_NONE) {
                            $activity = self::$ACTIVITY_STAND;
                        } else {
                            $activity = self::$ACTIVITY_MOVES;
                            $tmout = 0.25;
                        }
                        $this->SetValue('Activity', $activity);
                    }
                }
                if (isset($ret['Data']['data']['productData'])) {
                    $productDatas = $ret['Data']['data']['productData'];
                    foreach ($productDatas as $productData) {
                        if ($options['position_slider']) {
                            if (isset($productData['value']['percentage'])) {
                                $this->SetValue('Position', $productData['value']['percentage']);
                                break;
                            }
                        }
                        if ($options['rotation_slider']) {
                            if (isset($productData['value']['rotation'])) {
                                $this->SetValue('Rotation', $productData['value']['rotation']);
                                break;
                            }
                        }
                    }
                }
                if ($options['activity']) {
                    if ($this->GetValue('Activity') == self::$ACTIVITY_STAND) {
                        if ($options['position_slider']) {
                            $this->SetValue('TargetPosition', $this->GetValue('Position'));
                        }
                        if ($options['rotation_slider']) {
                            $this->SetValue('TargetRotation', $this->GetValue('Rotation'));
                        }
                    }
                }
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

        $interface = $this->ReadPropertyInteger('interface');
        if ($interface == self::$INTERFACE_WEBCONTROL) {
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
        } elseif ($interface == self::$INTERFACE_WEBCONTROLPRO) {
            $action = $this->actionType2action(self::$ACTION_TYPE_STOP);
            if ($action === false) {
                $this->SendDebug(__FUNCTION__, 'no action "' . $this->DecodeActionType(self::$ACTION_TYPE_STOP) . '" for product ' . $this->DecodeProduct($product), 0);
                return false;
            }
            $channel_id = $this->ReadPropertyInteger('channel_id');
            $data = [
                'actions' => [
                    [
                        'destinationId' => $channel_id,
                        'actionId'      => $action['id'],
                    ]
                ],
            ];
            $ret = $this->SendDataToIO(__FUNCTION__, $data);
            $state = $ret['State'];
            $this->SetValue('State', $state);
        } else {
            $state = self::$STATE_ERROR;
        }

        $this->SetValue('State', $state);
        return $state == self::$STATE_OK;
    }

    public function SendUp()
    {
        $this->SendDebug(__FUNCTION__, '', 0);

        $interface = $this->ReadPropertyInteger('interface');
        if ($interface == self::$INTERFACE_WEBCONTROL) {
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
        } elseif ($interface == self::$INTERFACE_WEBCONTROLPRO) {
            $action = $this->actionType2action(self::$ACTION_TYPE_PERCENTAGE);
            if ($action === false) {
                $this->SendDebug(__FUNCTION__, 'no action "' . $this->DecodeActionType(self::$ACTION_TYPE_PERCENTAGE) . '" for product ' . $this->DecodeProduct($product), 0);
                return false;
            }
            $position = isset($action['minValue']) ? $action['minValue'] : 0;

            $channel_id = $this->ReadPropertyInteger('channel_id');
            $data = [
                'actions' => [
                    [
                        'destinationId' => $channel_id,
                        'actionId'      => $action['id'],
                        'parameters'    => [
                            'percentage' => $position,
                        ],
                    ]
                ],
            ];
            $ret = $this->SendDataToIO(__FUNCTION__, $data);
            $state = $ret['State'];
            $this->SetValue('State', $state);
        } else {
            $state = self::$STATE_ERROR;
        }

        $this->SetValue('State', $state);
        return $state == self::$STATE_OK;
    }

    public function SendDown()
    {
        $this->SendDebug(__FUNCTION__, '', 0);

        $interface = $this->ReadPropertyInteger('interface');
        if ($interface == self::$INTERFACE_WEBCONTROL) {
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
        } elseif ($interface == self::$INTERFACE_WEBCONTROLPRO) {
            $action = $this->actionType2action(self::$ACTION_TYPE_PERCENTAGE);
            if ($action === false) {
                $this->SendDebug(__FUNCTION__, 'no action "' . $this->DecodeActionType(self::$ACTION_TYPE_PERCENTAGE) . '" for product ' . $this->DecodeProduct($product), 0);
                return false;
            }
            $position = isset($action['maxValue']) ? $action['maxValue'] : 100;

            $channel_id = $this->ReadPropertyInteger('channel_id');
            $data = [
                'actions' => [
                    [
                        'destinationId' => $channel_id,
                        'actionId'      => $action['id'],
                        'parameters'    => [
                            'percentage' => $position,
                        ],
                    ]
                ],
            ];
            $ret = $this->SendDataToIO(__FUNCTION__, $data);
            $state = $ret['State'];
            $this->SetValue('State', $state);
        } else {
            $state = self::$STATE_ERROR;
        }

        return $state == self::$STATE_OK;
    }

    public function SendPosition(int $position)
    {
        $this->SendDebug(__FUNCTION__, 'position=' . $position, 0);

        $interface = $this->ReadPropertyInteger('interface');
        if ($interface == self::$INTERFACE_WEBCONTROL) {
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
        } elseif ($interface == self::$INTERFACE_WEBCONTROLPRO) {
            $action = $this->actionType2action(self::$ACTION_TYPE_PERCENTAGE);
            if ($action === false) {
                $this->SendDebug(__FUNCTION__, 'no action "' . $this->DecodeActionType(self::$ACTION_TYPE_PERCENTAGE) . '" for product ' . $this->DecodeProduct($product), 0);
                return false;
            }
            if (isset($action['minValue']) && $position < $action['minValue']) {
                $position = $action['minValue'];
            }
            if (isset($action['maxValue']) && $position > $action['maxValue']) {
                $position = $action['maxValue'];
            }

            $channel_id = $this->ReadPropertyInteger('channel_id');
            $data = [
                'actions' => [
                    [
                        'destinationId' => $channel_id,
                        'actionId'      => $action['id'],
                        'parameters'    => [
                            'percentage' => $position,
                        ],
                    ]
                ],
            ];
            $ret = $this->SendDataToIO(__FUNCTION__, $data);
            $state = $ret['State'];
            $this->SetValue('State', $state);
        } else {
            $state = self::$STATE_ERROR;
        }

        return $state == self::$STATE_OK;
    }

    public function SendRotation(int $rotation)
    {
        $this->SendDebug(__FUNCTION__, 'rotation=' . $rotation, 0);

        $interface = $this->ReadPropertyInteger('interface');
        if ($interface == self::$INTERFACE_WEBCONTROLPRO) {
            $product = $this->ReadPropertyInteger('product');
            $action = $this->actionType2action(self::$ACTION_TYPE_ROTATION);
            if ($action === false) {
                $this->SendDebug(__FUNCTION__, 'no action "' . $this->DecodeActionType(self::$ACTION_TYPE_ROTATION) . '" for product ' . $this->DecodeProduct($product), 0);
                return false;
            }
            if (isset($action['minValue']) && $rotation < $action['minValue']) {
                $rotation = $action['minValue'];
            }
            if (isset($action['maxValue']) && $rotation > $action['maxValue']) {
                $rotation = $action['maxValue'];
            }

            $channel_id = $this->ReadPropertyInteger('channel_id');
            $data = [
                'actions' => [
                    [
                        'destinationId' => $channel_id,
                        'actionId'      => $action['id'],
                        'parameters'    => [
                            'rotation' => $rotation,
                        ],
                    ]
                ],
            ];
            $ret = $this->SendDataToIO(__FUNCTION__, $data);
            $state = $ret['State'];
            $this->SetValue('State', $state);
        } else {
            $state = self::$STATE_ERROR;
        }

        return $state == self::$STATE_OK;
    }

    public function SendSwitch(bool $state)
    {
        $this->SendDebug(__FUNCTION__, 'state=' . $this->bool2str($state), 0);

        $interface = $this->ReadPropertyInteger('interface');
        if ($interface == self::$INTERFACE_WEBCONTROLPRO) {
            $product = $this->ReadPropertyInteger('product');
            $action = $this->actionType2action(self::$ACTION_TYPE_SWITCH);
            if ($action === false) {
                $this->SendDebug(__FUNCTION__, 'no action "' . $this->DecodeActionType(self::$ACTION_TYPE_SWITCH) . '" for product ' . $this->DecodeProduct($product), 0);
                return false;
            }

            $channel_id = $this->ReadPropertyInteger('channel_id');
            $data = [
                'actions' => [
                    [
                        'destinationId' => $channel_id,
                        'actionId'      => $action['id'],
                        'parameters'    => [
                            'onOffState' => $state,
                        ],
                    ]
                ],
            ];
            $ret = $this->SendDataToIO(__FUNCTION__, $data);
            $state = $ret['State'];
            $this->SetValue('State', $state);
        } else {
            $state = self::$STATE_ERROR;
        }

        return $state == self::$STATE_OK;
    }

    public function SendDim(int $level)
    {
        $this->SendDebug(__FUNCTION__, 'level=' . $level, 0);

        $interface = $this->ReadPropertyInteger('interface');
        if ($interface == self::$INTERFACE_WEBCONTROLPRO) {
            $product = $this->ReadPropertyInteger('product');
            $action = $this->actionType2action(self::$ACTION_TYPE_PERCENTAGE);
            if ($action === false) {
                $this->SendDebug(__FUNCTION__, 'no action "' . $this->DecodeActionType(self::$ACTION_TYPE_PERCENTAGE) . '" for product ' . $this->DecodeProduct($product), 0);
                return false;
            }
            if (isset($action['minValue']) && $level < $action['minValue']) {
                $level = $action['minValue'];
            }
            if (isset($action['maxValue']) && $level > $action['maxValue']) {
                $level = $action['maxValue'];
            }

            $channel_id = $this->ReadPropertyInteger('channel_id');
            $data = [
                'actions' => [
                    [
                        'destinationId' => $channel_id,
                        'actionId'      => $action['id'],
                        'parameters'    => [
                            'percentage' => $level,
                        ],
                    ]
                ],
            ];
            $ret = $this->SendDataToIO(__FUNCTION__, $data);

            $state = $ret['State'];
            $this->SetValue('State', $state);
        } else {
            $state = self::$STATE_ERROR;
        }

        return $state == self::$STATE_OK;
    }

    public function QueryDeviceStatus()
    {
        $this->SendDebug(__FUNCTION__, '', 0);

        $interface = $this->ReadPropertyInteger('interface');
        if ($interface == self::$INTERFACE_WEBCONTROL) {
            $room_id = $this->ReadPropertyInteger('room_id');
            $channel_id = $this->ReadPropertyInteger('channel_id');

            $data = [
                'room_id'    => $room_id,
                'channel_id' => $channel_id,
            ];
            $ret = $this->SendDataToIO(__FUNCTION__, $data);
        } elseif ($interface == self::$INTERFACE_WEBCONTROLPRO) {
            $channel_id = $this->ReadPropertyInteger('channel_id');

            $data = [
                'channel_id' => $channel_id,
            ];
            $ret = $this->SendDataToIO(__FUNCTION__, $data);
        } else {
            $ret = false;
        }
        return $ret;
    }
}
