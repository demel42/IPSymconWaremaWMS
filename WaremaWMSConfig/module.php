<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/CommonStubs/common.php'; // globale Funktionen
require_once __DIR__ . '/../libs/local.php';  // lokale Funktionen

class WaremaWMSConfig extends IPSModule
{
    use StubsCommonLib;
    use WaremaWMSLocalLib;

    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyInteger('ImportCategoryID', 0);

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

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $refs = $this->GetReferenceList();
        foreach ($refs as $ref) {
            $this->UnregisterReference($ref);
        }
        $propertyNames = ['ImportCategoryID'];
        foreach ($propertyNames as $name) {
            $oid = $this->ReadPropertyInteger($name);
            if ($oid >= 10000) {
                $this->RegisterReference($oid);
            }
        }

        $this->SetStatus(IS_ACTIVE);
    }

    private function SetLocation()
    {
        $catID = $this->ReadPropertyInteger('ImportCategoryID');
        $tree_position = [];
        if ($catID >= 10000 && IPS_ObjectExists($catID)) {
            $tree_position[] = IPS_GetName($catID);
            $parID = IPS_GetObject($catID)['ParentID'];
            while ($parID > 0) {
                if ($parID > 0) {
                    $tree_position[] = IPS_GetName($parID);
                }
                $parID = IPS_GetObject($parID)['ParentID'];
            }
            $tree_position = array_reverse($tree_position);
        }
        $this->SendDebug(__FUNCTION__, 'tree_position=' . print_r($tree_position, true), 0);
        return $tree_position;
    }

    private function getConfiguratorValues()
    {
        $config_list = [];

        if ($this->CheckStatus() == self::$STATUS_INVALID) {
            $this->SendDebug(__FUNCTION__, $this->GetStatusText() . ' => skip', 0);
            return $config_list;
        }

        $this->SetStatus(IS_ACTIVE);

        $data = [
            'DataID'   => '{A8C43E67-9C5C-8A22-1F46-69EC56138C81}',
            'Function' => 'GetDevices',
        ];
        $ret = $this->SendDataToParent(json_encode($data));
        $r = json_decode($ret, true);
        $devices = isset($r['Data']) ? $r['Data'] : false;
        $this->SendDebug(__FUNCTION__, 'devices=' . print_r($devices, true), 0);
        if (is_array($devices) && count($devices)) {
            $guid = '{DAC4B9CA-4754-8292-3B64-6A825163AB09}';
            $instIDs = IPS_GetInstanceListByModuleID($guid);
            foreach ($devices as $device) {
                $this->SendDebug(__FUNCTION__, 'device=' . print_r($device, true), 0);
                $room_id = $device['room_id'];
                $channel_id = $device['channel_id'];
                $room_name = $device['room_name'];
                $channel_name = $device['channel_name'];
                $product = (int) $device['product'];
                $product_name = $this->DecodeProduct($product);

                $instanceID = 0;
                foreach ($instIDs as $instID) {
                    if (IPS_GetProperty($instID, 'room_id') == $room_id && IPS_GetProperty($instID, 'channel_id') == $channel_id) {
                        $this->SendDebug(__FUNCTION__, 'device found: ' . utf8_decode(IPS_GetName($instID)) . ' (' . $instID . ')', 0);
                        $instanceID = $instID;
                        break;
                    }
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
                            'location'      => $this->SetLocation(),
                            'info'          => 'Warema WMS ' . $product,
                            'configuration' => [
                                'room_id'    => $room_id,
                                'channel_id' => $channel_id,
                                'product'    => $product,
                            ]
                        ],
                    ],
                ];

                $config_list[] = $entry;
                $this->SendDebug(__FUNCTION__, 'entry=' . print_r($entry, true), 0);
            }
        }
        return $config_list;
    }

    private function GetFormElements()
    {
        $formElements = [];

        $formElements[] = [
            'type'    => 'Label',
            'caption' => 'Warema WMS configurator'
        ];

        @$s = $this->CheckConfiguration();
        if ($s != '') {
            $formElements[] = [
                'type'    => 'Label',
                'caption' => $s,
            ];
            $formElements[] = [
                'type'    => 'Label',
            ];
        }

        $formElements[] = [
            'name'    => 'ImportCategoryID',
            'type'    => 'SelectCategory',
            'caption' => 'Import category'
        ];

        $entries = $this->getConfiguratorValues();
        $configurator = [
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
            'values' => $entries
        ];
        $formElements[] = $configurator;

        return $formElements;
    }

    private function GetFormActions()
    {
        $formActions = [];

        $formActions[] = $this->GetInformationForm();
        $formActions[] = $this->GetReferencesForm();

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
