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

    /*
        private static $BEDIENTYP_LICHT1 = 0;
        private static $BEDIENTYP_FENSTER = 1;
        private static $BEDIENTYP_RAFFSTORE = 2;
        private static $BEDIENTYP_ROLLLADEN = 3;
        private static $BEDIENTYP_MARKISE = 4;
        private static $BEDIENTYP_VOLANTMARKISE = 5;
        private static $BEDIENTYP_LICHT2 = 6;
        private static $BEDIENTYP_LICHT3 = 7;
        private static $BEDIENTYP_TOTMANN = 8;
        private static $BEDIENTYP_LAST = 9;
     */

    private static $STATE_OK = 0;
    private static $STATE_CHANNEL_UNREACHABLE = 1;
    private static $STATE_GATEWAY_UNREACHABLE = 2;
    private static $STATE_BLOCKED_WIND = 3;
    private static $STATE_BLOCKED_RAIN = 4;
    private static $STATE_ERROR = 9;

    private static $CONTROL_STOP = 0;
    private static $CONTROL_UP = 1;
    private static $CONTROL_DOWN = 2;

    private static $ACTIVITY_STAND = 0;
    private static $ACTIVITY_MOVES = 1;

    public function InstallVarProfiles(bool $reInstall = false)
    {
        if ($reInstall) {
            $this->SendDebug(__FUNCTION__, 'reInstall=' . $this->bool2str($reInstall), 0);
        }

        $associations = [
            ['Wert' => self::$STATE_OK, 'Name' => $this->Translate('Ok'), 'Farbe' => -1],
            ['Wert' => self::$STATE_CHANNEL_UNREACHABLE, 'Name' => $this->Translate('No reaction'), 'Farbe' => -1],
            ['Wert' => self::$STATE_GATEWAY_UNREACHABLE, 'Name' => $this->Translate('Gateway error'), 'Farbe' => -1],
            ['Wert' => self::$STATE_BLOCKED_WIND, 'Name' => $this->Translate('Wind alarm'), 'Farbe' => -1],
            ['Wert' => self::$STATE_BLOCKED_RAIN, 'Name' => $this->Translate('Rain alarm '), 'Farbe' => -1],
            ['Wert' => self::$STATE_ERROR, 'Name' => $this->Translate('Error'), 'Farbe' => -1],
        ];
        $this->CreateVarProfile('WaremaWMS.State', VARIABLETYPE_INTEGER, '', 0, 0, 0, 0, '', $associations, $reInstall);

        $associations = [
            ['Wert' => self::$CONTROL_UP, 'Name' => $this->Translate('Retract'), 'Farbe' => -1],
            ['Wert' => self::$CONTROL_STOP, 'Name' => $this->Translate('Stop'), 'Farbe' => -1],
            ['Wert' => self::$CONTROL_DOWN, 'Name' => $this->Translate('Extend'), 'Farbe' => -1],
        ];
        $this->CreateVarProfile('WaremaWMS.ControlAwning', VARIABLETYPE_INTEGER, '', 0, 0, 0, 0, '', $associations, $reInstall);

        $associations = [
            ['Wert' => self::$CONTROL_UP, 'Name' => $this->Translate('Up'), 'Farbe' => -1],
            ['Wert' => self::$CONTROL_STOP, 'Name' => $this->Translate('Stop'), 'Farbe' => -1],
            ['Wert' => self::$CONTROL_DOWN, 'Name' => $this->Translate('Down'), 'Farbe' => -1],
        ];
        $this->CreateVarProfile('WaremaWMS.ControlBlind', VARIABLETYPE_INTEGER, '', 0, 0, 0, 0, '', $associations, $reInstall);

        $associations = [
            ['Wert' => self::$ACTIVITY_STAND, 'Name' => $this->Translate('Stand'), 'Farbe' => -1],
            ['Wert' => self::$ACTIVITY_MOVES, 'Name' => $this->Translate('Moves'), 'Farbe' => -1],
        ];
        $this->CreateVarProfile('WaremaWMS.Activity', VARIABLETYPE_INTEGER, '', 0, 0, 0, 0, '', $associations, $reInstall);

        $this->CreateVarProfile('WaremaWMS.Position', VARIABLETYPE_INTEGER, ' %', 0, 100, 1, 0, 'Intensity', [], $reInstall);
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
            $s = $this->Translate('unknown language') . ' ' . $lang;
        }
        return $s;
    }

    private static $PRODUCT_RAFFSTORE = 0;
    private static $PRODUCT_JALOUSIE_INNEN = 1;
    private static $PRODUCT_ROLLLADEN = 2;
    private static $PRODUCT_MARKISE = 3;
    private static $PRODUCT_MARKISE_1_VOLANT = 4;
    private static $PRODUCT_MARKISE_INT_WIND = 5;
    private static $PRODUCT_MARKISE_1_VOLANT_INT_WIND = 6;
    private static $PRODUCT_WINTERGARTEN_MARKISE = 7;
    private static $PRODUCT_FASSADEN_MARKISE = 8;
    private static $PRODUCT_FALLARM_MARKISE = 9;
    private static $PRODUCT_SENKRECHT_MARKISE = 10;
    private static $PRODUCT_MARKISOLETTE = 11;
    private static $PRODUCT_FALTSTORE_INNEN = 12;
    private static $PRODUCT_ROLLO_INNEN = 13;
    private static $PRODUCT_VERTIKAL_JALOUSIE_INNEN = 14;
    private static $PRODUCT_FENSTER = 15;
    private static $PRODUCT_LICHT_SCHALTEN = 16;
    private static $PRODUCT_LAST_SCHALTEN = 17;
    private static $PRODUCT_LICHT_DIMMEN = 18;
    private static $PRODUCT_LAST_DIMMEN = 19;
    private static $PRODUCT_STECKDOSE_SCHALTEN = 20;
    private static $PRODUCT_VOLANT = 21;
    private static $PRODUCT_MARKISE_2_VOLANT = 22;
    private static $PRODUCT_MARKISE_2_VOLANT_INT_WIND = 23;
    private static $PRODUCT_SONNENSEGEL = 24;
    private static $PRODUCT_PERGOLAMARISE = 25;
    private static $PRODUCT_LED_DIMMER = 26;

    private function ProductMapping()
    {
        return [
            self::$PRODUCT_MARKISE          => 'Awning',
            self::$PRODUCT_MARKISE_INT_WIND => 'Awning with windsensor',
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
            $s = $this->Translate('unknown product') . ' ' . $product;
        }
        return $s;
    }

    private static $INTERFACE_WEBCONTROL = 0;

    private function InterfaceMapping()
    {
        return [
            self::$INTERFACE_WEBCONTROL => 'WebControl',
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
}
