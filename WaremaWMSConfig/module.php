<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/common.php';
require_once __DIR__ . '/../libs/local.php';

class WaremaWMSConfig extends IPSModule
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

        if (IPS_GetKernelVersion() < 7.0) {
            $this->RegisterPropertyInteger('ImportCategoryID', 0);
        }

        $this->RegisterAttributeString('UpdateInfo', json_encode([]));
        $this->RegisterAttributeString('ModuleStats', json_encode([]));

        $this->ConnectParent('{6A9BBD57-8473-682D-4ABF-009AE8584B2B}');
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $propertyNames = [];
        if (IPS_GetKernelVersion() < 7.0) {
            $propertyNames[] = 'ImportCategoryID';
        }
        $this->MaintainReferences($propertyNames);

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

        $this->MaintainStatus(IS_ACTIVE);
    }

    private function getConfiguratorValues()
    {
        $entries = [];

        if ($this->CheckStatus() == self::$STATUS_INVALID) {
            $this->SendDebug(__FUNCTION__, $this->GetStatusText() . ' => skip', 0);
            return $entries;
        }

        $this->MaintainStatus(IS_ACTIVE);

        if (IPS_GetKernelVersion() < 7.0) {
            $catID = $this->ReadPropertyInteger('ImportCategoryID');
            $location = $this->GetConfiguratorLocation($catID);
        } else {
            $location = '';
        }

        $data = [
            'DataID'   => '{A8C43E67-9C5C-8A22-1F46-69EC56138C81}',
            'Function' => 'GetDevices',
        ];
        $ret = $this->SendDataToParent(json_encode($data));
        $r = json_decode($ret, true);
        $devices = isset($r['Data']) ? $r['Data'] : false;
        $this->SendDebug(__FUNCTION__, 'devices=' . print_r($devices, true), 0);

        $guid = '{DAC4B9CA-4754-8292-3B64-6A825163AB09}';
        $instIDs = IPS_GetInstanceListByModuleID($guid);

        if (is_array($devices) && count($devices)) {
            foreach ($devices as $device) {
                $this->SendDebug(__FUNCTION__, 'device=' . print_r($device, true), 0);

                $room_id = $device['room_id'];
                $channel_id = $device['channel_id'];
                $room_name = $device['room_name'];
                $channel_name = $device['channel_name'];
                $product = (int) $device['product'];
                $product_name = $this->DecodeProduct($product);
                $interface = $device['interface'];
                $actions = $device['actions'];

                $instanceID = 0;
                foreach ($instIDs as $instID) {
                    if (@IPS_GetProperty($instID, 'room_id') == $room_id && @IPS_GetProperty($instID, 'channel_id') == $channel_id) {
                        $this->SendDebug(__FUNCTION__, 'instance found: ' . IPS_GetName($instID) . ' (' . $instID . ')', 0);
                        $instanceID = $instID;
                        break;
                    }
                }

                if ($instanceID && IPS_GetInstance($instanceID)['ConnectionID'] != IPS_GetInstance($this->InstanceID)['ConnectionID']) {
                    continue;
                }

                $entry = [
                    'instanceID'   => $instanceID,
                    'name'         => $room_name . '.' . $channel_name,
                    'room_id'      => $room_id,
                    'channel_id'   => $channel_id,
                    'room_name'    => $room_name,
                    'channel_name' => $channel_name,
                    'product_name' => $product_name,
                    'create'       => [
                        [
                            'moduleID'      => $guid,
                            'location'      => $location,
                            'info'          => 'Warema WMS ' . $product_name,
                            'configuration' => [
                                'room_id'    => $room_id,
                                'channel_id' => $channel_id,
                                'interface'  => $interface,
                                'product'    => $product,
                                'actions'    => $actions,
                            ]
                        ],
                    ],
                ];
                $entries[] = $entry;
                $this->SendDebug(__FUNCTION__, 'instanceID=' . $instanceID . ', entry=' . print_r($entry, true), 0);
            }
        }
        foreach ($instIDs as $instID) {
            $fnd = false;
            foreach ($entries as $entry) {
                if ($entry['instanceID'] == $instID) {
                    $fnd = true;
                    break;
                }
            }
            if ($fnd) {
                continue;
            }

            if (IPS_GetInstance($instID)['ConnectionID'] != IPS_GetInstance($this->InstanceID)['ConnectionID']) {
                continue;
            }

            $room_id = IPS_GetProperty($instID, 'room_id');
            $channel_id = IPS_GetProperty($instID, 'channel_id');
            $name = IPS_GetName($instID);
            $room_name = '';
            $channel_name = '';
            $product = (int) IPS_GetProperty($instID, 'product');
            $product_name = $this->DecodeProduct($product);

            $entry = [
                'instanceID'   => $instID,
                'name'         => $name,
                'room_id'      => $room_id,
                'channel_id'   => $channel_id,
                'room_name'    => $room_name,
                'channel_name' => $channel_name,
                'product_name' => $product_name,
            ];
            $entries[] = $entry;
            $this->SendDebug(__FUNCTION__, 'lost: instanceID=' . $instID . ', entry=' . print_r($entry, true), 0);
        }
        return $entries;
    }

    private function GetFormElements()
    {
        $formElements = $this->GetCommonFormElements('Warema WMS configurator');

        if ($this->GetStatus() == self::$IS_UPDATEUNCOMPLETED) {
            return $formElements;
        }

        if (IPS_GetKernelVersion() < 7.0) {
            $formElements[] = [
                'name'    => 'ImportCategoryID',
                'type'    => 'SelectCategory',
                'caption' => 'Import category'
            ];
        }

        $entries = $this->getConfiguratorValues();
        $formElements[] = [
            'name'    => 'devices',
            'type'    => 'Configurator',
            'caption' => 'Devices',

            'rowCount' => count($entries),

            'add'     => false,
            'delete'  => false,
            'columns' => [
                [
                    'caption' => 'Room ID',
                    'name'    => 'room_id',
                    'width'   => '100px'
                ],
                [
                    'caption' => 'Channel ID',
                    'name'    => 'channel_id',
                    'width'   => '100px'
                ],
                [
                    'caption' => 'Name',
                    'name'    => 'name',
                    'width'   => 'auto'
                ],
                [
                    'caption' => 'Product',
                    'name'    => 'product_name',
                    'width'   => '400px'
                ]
            ],
            'values' => $entries,
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

        $formActions[] = $this->GetInformationFormAction();
        $formActions[] = $this->GetReferencesFormAction();

        return $formActions;
    }

    public function RequestAction($Ident, $Value)
    {
        if ($this->CommonRequestAction($Ident, $Value)) {
            return;
        }
        switch ($Ident) {
            default:
                $this->SendDebug(__FUNCTION__, 'invalid ident ' . $Ident, 0);
                break;
        }
    }
}
