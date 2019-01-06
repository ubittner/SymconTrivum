<?php

/*
 * @module      Trivum FlexLine
 *
 * @prefix      UBTFL
 *
 * @file        module.php
 *
 * @developer   Ulrich Bittner
 * @project     A joint project of Normen Thiel and Ulrich Bittner
 * @copyright   (c) 2019
 * @license    	CC BY-NC-SA 4.0
 *              https://creativecommons.org/licenses/by-nc-sa/4.0/
 *
 * @version     1.04-1
 * @date        2019-01-06, 11:00
 * @lastchange  2019-01-06, 11:00
 *
 * @see         https://github.com/ubittner/SymconTrivum
 *
 * @guids       Library
 *              {2BF5E234-467B-40D5-A156-C0FA9A728352}
 *
 *              FlexLine
 *             	{CFAA5028-F205-4FE6-B86C-4F5E1EDD4CCD}
 *
 * @changelog   2019-01-06, 11:00, initial version 1.04-1
 *
 */

// Declare
declare(strict_types=1);

// Include
include_once __DIR__ . '/helper/UBTFL_Autoload.php';

class TrivumFlexLine extends IPSModule
{
    public function Create()
    {
        // Never delete this line!
        parent::Create();

        // Register properties
        $this->RegisterPropertyString('DeviceIP', '');
        $this->RegisterPropertyInteger('Timeout', 1000);
        $this->RegisterPropertyInteger('ZoneID', 0);
        $this->RegisterPropertyString('ZoneName', '');
        $this->RegisterPropertyString('FavoriteList', '');
        $this->RegisterPropertyString('ZoneMembersList', '');
    }

    public function ApplyChanges()
    {
        // Wait until IP-Symcon is started
        $this->RegisterMessage(0, IPS_KERNELSTARTED);

        // Never delete this line!
        parent::ApplyChanges();

        // Check kernel runlevel
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }

        // Register profiles and variables

        // Zone power
        $zonePower = 'UBTFL.' . $this->InstanceID . '.ZonePower';
        if (!IPS_VariableProfileExists($zonePower)) {
            IPS_CreateVariableProfile($zonePower, 0);
        }
        IPS_SetVariableProfileIcon($zonePower, 'Power');
        IPS_SetVariableProfileAssociation($zonePower, 0, $this->Translate('Off'), '', 0xFF0000);
        IPS_SetVariableProfileAssociation($zonePower, 1, $this->Translate('On'), '', 0x00FF00);
        $this->RegisterVariableBoolean('ZonePower', 'Zone', $zonePower, 1);
        $this->EnableAction('ZonePower');

        // Audio sources
        $this->CreateAudioSourcesProfile();
        $this->CreateAudioFavoritesProfile();
        $audioSources = 'UBTFL.' . $this->InstanceID . '.AudioSources';
        $this->RegisterVariableInteger('AudioSources', $this->Translate('AudioSources'), $audioSources, 2);
        $this->EnableAction('AudioSources');
        IPS_SetIcon($this->GetIDForIdent('AudioSources'), 'Melody');
        $favorites = json_decode($this->ReadPropertyString('FavoriteList'));
        if (empty($favorites)) {
            $this->SetValue('AudioSources', -1);
        } else {
            $this->SetValue('AudioSources', 1);
        }

        // Volume slider
        $volumeSlider = 'UBTFL.' . $this->InstanceID . '.VolumeSlider';
        if (!IPS_VariableProfileExists($volumeSlider)) {
            IPS_CreateVariableProfile($volumeSlider, 1);
        }
        IPS_SetVariableProfileIcon($volumeSlider, 'Speaker');
        IPS_SetVariableProfileText($volumeSlider, '', '%');
        IPS_SetVariableProfileValues($volumeSlider, 0, 99, 1);
        $this->RegisterVariableInteger('VolumeSlider', $this->Translate('Volume'), $volumeSlider, 3);
        $this->SetValue('VolumeSlider', 10);
        $this->EnableAction('VolumeSlider');

        // Zone Members
        $this->CreateZoneMembersProfile();
        $zoneMembers = 'UBTFL.' . $this->InstanceID . '.ZoneMembers';
        $this->RegisterVariableInteger('ZoneMembers', $this->Translate('Zone members'), $zoneMembers, 4);
        $this->EnableAction('ZoneMembers');
        $this->SetValue('ZoneMembers', -1);

        // Group
        $this->RegisterVariableString('Group', $this->Translate('Group'), '', 5);
        IPS_SetIcon($this->GetIDForIdent('Group'), 'Information');

        // Group type
        $this->RegisterVariableString('GroupType', $this->Translate('Group type'), '', 6);
        IPS_SetIcon($this->GetIDForIdent('GroupType'), 'Information');

        $this->ValidateConfiguration();
    }

    public function Destroy()
    {
        // Never delete this line!
        parent::Destroy();

        // Delete profiles
        $this->DeleteProfiles();
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        /*
        // Log message
        IPS_LogMessage('MessageSink', 'Message from SenderID ' . $SenderID . ' with Message ' . $Message . "\r\n Data: " . print_r($Data, true));
        */
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;
        }
    }

    /**
     * Apply changes when the kernel is ready.
     */
    protected function KernelReady()
    {
        $this->ApplyChanges();
    }

    //#################### Configuration form

    public function GetConfigurationForm()
    {
        $formdata = json_decode(file_get_contents(__DIR__ . '/form.json'));
        // Zone members
        $ZoneMembers = json_decode($this->ReadPropertyString('ZoneMembersList'));
        if (!empty($ZoneMembers)) {
            $zoneMemberStatus = true;
            foreach ($ZoneMembers as $currentKey => $currentArray) {
                $rowColor = '';
                foreach ($ZoneMembers as $searchKey => $searchArray) {
                    // Search for double entries
                    // Check position
                    if ($searchArray->Position == $currentArray->Position) {
                        if ($searchKey != $currentKey) {
                            $rowColor = '#FFC0C0';
                            $zoneMemberStatus = false;
                        }
                    }
                    // Check zone id
                    if ($searchArray->ZoneID == $currentArray->ZoneID) {
                        if ($searchKey != $currentKey) {
                            $rowColor = '#FFC0C0';
                            $zoneMemberStatus = false;
                        }
                    }
                }
                // Check entries
                if (($currentArray->Position == '') || ($currentArray->Description == '') || ($currentArray->InstanceID == 0)) {
                    $rowColor = '#FFC0C0';
                    $zoneMemberStatus = false;
                }
                $formdata->elements[4]->items[1]->values[] = array('Position' => $currentArray->Position, 'ZoneID' => $currentArray->ZoneID, 'Description' => $currentArray->Description, 'InstanceID' => $currentArray->InstanceID, 'rowColor' => $rowColor);
                if ($zoneMemberStatus == false) {
                    $this->SetStatus(2411);
                }
            }
        }
        // Favourites
        $favorites = json_decode($this->ReadPropertyString('FavoriteList'));
        if (!empty($favorites)) {
            $favouriteStatus = true;
            foreach ($favorites as $currentKey => $currentArray) {
                $rowColor = '';
                foreach ($favorites as $searchKey => $searchArray) {
                    // Search for double entries
                    // Check position
                    if ($searchArray->Position == $currentArray->Position) {
                        if ($searchKey != $currentKey) {
                            $rowColor = '#FFC0C0';
                            $favouriteStatus = false;
                        }
                    }
                    // Check favorite
                    if ($searchArray->Favorite == $currentArray->Favorite) {
                        if ($searchKey != $currentKey) {
                            $rowColor = '#FFC0C0';
                            $favouriteStatus = false;
                        }
                    }
                }
                // Check entries
                if (($currentArray->Position == '') || ($currentArray->Description == '') || ($currentArray->Volume < -1 || $currentArray->Volume > 100)) {
                    $rowColor = '#FFC0C0';
                    $favouriteStatus = false;
                }
                $formdata->elements[3]->items[2]->values[] = array('Position' => $currentArray->Position, 'Favorite' => $currentArray->Favorite, 'Description' => $currentArray->Description, 'Volume' => $currentArray->Volume, 'rowColor' => $rowColor);
                if ($favouriteStatus == false) {
                    $this->SetStatus(2311);
                }
            }
        }
        return json_encode($formdata);
    }

    //#################### Request action

    public function RequestAction($Ident, $Value)
    {
        try {
            switch ($Ident) {
                case 'ZonePower':
                    $this->ToggleZonePower($Value);
                    break;
                case 'AudioSources':
                    $this->SelectFavorite($Value);
                    break;
                case 'VolumeSlider':
                    $this->SetZoneVolume($Value);
                    break;
                case "ZoneMembers":
                    $this->SelectZoneMember($Value);
                    break;
                default:
                    throw new Exception('Invalid Ident');
            }
        } catch (Exception $e) {
            IPS_LogMessage('SymconTrivum', $e->getMessage());
        }
    }

    //#################### Module functions

    /**
     * Shows the system configuration of a Trivum device.
     */
    public function ShowSystemConfiguration()
    {
        try {
            $device = $this->CheckDevice();
            if (!empty($device)) {
                $deviceIP = $this->ReadPropertyString('DeviceIP');
                $url = 'http://' . $deviceIP . '/print/system/sampleHttprequest';
                echo $url;
            } else {
                echo $this->Translate("Unable to reach the device.\nPlease check network configuration!");
            }
        } catch (Exception $e) {
            $this->CreateMessageLogEntry($e->getMessage());
        }
    }

    /**
     * Opens the setup page of a Trivum device.
     */
    public function SetupSystem()
    {
        try {
            $device = $this->CheckDevice();
            if (!empty($device)) {
                $deviceIP = $this->ReadPropertyString('DeviceIP');
                $url = 'http://' . $deviceIP . '/setup';
                echo $url;
            } else {
                echo $this->Translate("Unable to reach the device.\nPlease check network configuration!");
            }
        } catch (Exception $e) {
            $this->CreateMessageLogEntry($e->getMessage());
        }
    }

    /**
     * Opens the frontend of a Trivum device.
     */
    public function ShowWebUI()
    {
        try {
            $device = $this->CheckDevice();
            if (!empty($device)) {
                $deviceIP = $this->ReadPropertyString('DeviceIP');
                $url = 'http://' . $deviceIP;
                echo $url;
            } else {
                echo $this->Translate("Unable to reach the device.\nPlease check network configuration!");
            }
        } catch (Exception $e) {
            $this->CreateMessageLogEntry($e->getMessage());
        }
    }

    /**
     * Get the zone name and the favorites of a Trivum device.
     */
    public function GetTrivumData()
    {
        try {
            // Get zone name
            $endpoint = '/xml/zone/getAll.xml';
            $data = $this->SendData($endpoint);
            if (!empty($data)) {
                $zones = $data->zone;
                $zoneID = $this->ReadPropertyInteger('ZoneID');
                $zoneName = 'Trivium Output Zone';
                foreach ($zones as $key => $zone) {
                    if ($zone->id == $zoneID) {
                        $zoneName = (string)$zone->description;
                    }
                }
                IPS_SetProperty($this->InstanceID, 'ZoneName', $zoneName);
                IPS_SetName($this->InstanceID, $zoneName);
                // Get favorites
                $endpoint = '/api/v1/trivum/favorite';
                $data = $this->SendData($endpoint);
                $favoriteList = array();
                $i = 0;
                foreach ($data->row as $favorite) {
                    $favoriteNumber = $favorite->number + 1;
                    $name = str_replace('%20', ' ', $favorite->info2);
                    $favoriteList[$i]['Favorite'] = (string)$favoriteNumber;
                    $favoriteList[$i]['Description'] = (string)$name;
                    $favoriteList[$i]['Volume'] = (integer)10;
                    $i++;
                }
                IPS_SetProperty($this->InstanceID, 'FavoriteList', json_encode($favoriteList));
                if (IPS_HasChanges($this->InstanceID)) {
                    IPS_ApplyChanges($this->InstanceID);
                }
                echo $this->Translate("Configuration was read out successfully!");
            }
        } catch (Exception $e) {
            $this->CreateMessageLogEntry($e->getMessage());
        }
    }

    /**
     * Toggles the zone  off / on.
     *
     * @param bool $State
     *
     * @return null|SimpleXMLElement
     */
    public function ToggleZonePower(bool $State)
    {
        $result = null;
        try {
            $status = 'off';
            switch ($State) {
                case true:
                    $status = 'on';
                    break;
                case false:
                    $status = 'off';
                    break;
            }
            // Power on
            if ($State == true) {
                $favorite = (int)$this->GetValue('AudioSources');
                $result = $this->SelectFavorite($favorite);
            } else {
                // Reset group
                $this->SelectZoneMember(-1);
                $this->SetValue('ZoneMembers', -1);
                // Power off
                $zoneID = $this->ReadPropertyInteger('ZoneID');
                $endpoint = '/xml/zone/set.xml?zone=' . $zoneID . '&status=' . $status;
                $data = $this->SendData($endpoint);
                if (!empty($data)) {
                    if ($data->userdata[0] == 0) {
                        $this->SetValue('ZonePower', $State);
                        $result = $data;
                    } else {
                        $this->CreateMessageLogEntry($this->Translate('An error has occurred when switching the device'));
                    }

                }
            }
        } catch (Exception $e) {
            $this->CreateMessageLogEntry($e->getMessage());
        }
        return $result;
    }

    /**
     * Selects a favorite of a Trivum device.
     *
     * @param int $Value
     *
     * @return null|SimpleXMLElement
     */
    public function SelectFavorite(int $Value)
    {
        $result = null;
        try {
            $zoneID = $this->ReadPropertyInteger('ZoneID');
            // Get value from audio favorites profile
            $profile = 'UBTFL.' . $this->InstanceID . '.AudioFavorites';
            $associations = IPS_GetVariableProfile($profile)['Associations'];
            $favorite = null;
            foreach ($associations as $association) {
                if ($association['Value'] == $Value) {
                    if ($Value != -1) {
                        $favorite = $association['Name'];
                    }
                }
            }
            if (!is_null($favorite)) {
                $favoriteID = $favorite - 1;
                $endpoint = '/xml/zone/playFavorite.xml?id=' . $zoneID . '&favorite=' . $favoriteID;
                $data = $this->SendData($endpoint);
                if (!empty($data)) {
                    if ($data->userdata[0] == 0) {
                        $this->SetValue('AudioSources', $Value);
                        $this->SetValue('ZonePower', true);
                        $this->SetStandardVolume();
                        $result = $data;
                    } else {
                        $this->CreateMessageLogEntry($this->Translate('An error has occurred when selecting a favorite'));
                    }
                }
            }
        } catch (Exception $e) {
            $this->CreateMessageLogEntry($e->getMessage());
        }
        return $result;
    }

    /**
     * Sets the volume for the zone.
     *
     * @param int $Volume
     *
     * @return null|SimpleXMLElement
     */
    public function SetZoneVolume(int $Volume)
    {
        $result = null;
        try {
            if ($Volume > 100) {
                $Volume = 100;
            }
            $zoneID = $this->ReadPropertyInteger('ZoneID');
            $endpoint = '/xml/zone/setVolume.xml?id=' . $zoneID . '&groupMemberVolume=' . $Volume . '&absolute';
            $data = $this->SendData($endpoint);
            if (!empty($data)) {
                if ($data->userdata[0] == 0) {
                    $this->SetValue('VolumeSlider', $Volume);
                    $result = $data;
                } else {
                    $this->CreateMessageLogEntry($this->Translate('An error has occurred when setting volume'));
                }
            }
        } catch (Exception $e) {
            $this->CreateMessageLogEntry($e->getMessage());
        }
        return $result;
    }

    /**
     * Selects a zone member.
     *
     * @param int $MemberZoneID
     */
    public function SelectZoneMember(int $MemberZoneID)
    {
        $masterZoneID = $this->ReadPropertyInteger('ZoneID');
        $zoneMembers = json_decode($this->ReadPropertyString('ZoneMembersList'));
        if ($MemberZoneID != -1) {
            $groupStatus = $this->GetGroupStatus();
            if (!is_null($groupStatus)) {
                $zones = $groupStatus->row;
                // Get group number of this zone
                $groupNumber = 255;
                foreach ($zones as $zone) {
                    $zoneID = (integer)$zone->id;
                    if ($zoneID == $masterZoneID) {
                        $groupNumber = (integer)$zone->group;
                    }
                }
                // First grouping
                if ($groupNumber == 255) {
                    $newZoneMembers = '--------';
                    $newZoneMembers = substr_replace($newZoneMembers, '+', $masterZoneID, 1);
                    $newZoneMembers = substr_replace($newZoneMembers, '+', $MemberZoneID, 1);
                    $endpoint = '/xml/zone/createGroup.xml?zone=' . $MemberZoneID . '&oldgroup=' . $masterZoneID . '&members=' . $newZoneMembers;
                    $this->SendData($endpoint);
                    // Get group
                    $groupStatus = $this->GetGroupStatus();
                    $zones = $groupStatus->row;
                    $groupNumber = 255;
                    foreach ($zones as $zone) {
                        $zoneID = (integer)$zone->id;
                        if ($zoneID == $masterZoneID) {
                            $groupNumber = (integer)$zone->group;
                        }
                    }
                    // Zone power
                    $this->SetValue('ZonePower', true);
                    // Group
                    $this->SetValue('Group', $groupNumber);
                    // Group type master
                    $this->SetValue('GroupType', 'Master');
                    // Group type slave
                    foreach ($zoneMembers as $zoneMember) {
                        $zoneID = (integer)$zoneMember->ZoneID;
                        if ($MemberZoneID == $zoneID) {
                            $zoneInstanceID = (integer)$zoneMember->InstanceID;
                            // Zone power
                            $zonePower = IPS_GetObjectIDByIdent('ZonePower', $zoneInstanceID);
                            SetValue($zonePower, true);
                            // Group
                            $group = IPS_GetObjectIDByIdent('Group', $zoneInstanceID);
                            SetValue($group, $groupNumber);
                            // Group type
                            $groupType = IPS_GetObjectIDByIdent('GroupType', $zoneInstanceID);
                            SetValue($groupType, 'Slave');
                        }
                    }

                }
                // Add another zone
                if ($groupNumber >= 0 && $groupNumber < 255) {
                    $newZoneMembers = '--------';
                    foreach ($zones as $zone) {
                        $zoneID = (integer)$zone->id;
                        $zoneGroupNumber = (integer)$zone->group;
                        if ($zoneGroupNumber == $groupNumber) {
                            $newZoneMembers = substr_replace($newZoneMembers, '+', $zoneID, 1);
                            // Group type slave
                            foreach ($zoneMembers as $zoneMember) {
                                $zoneID = (integer)$zoneMember->ZoneID;
                                if ($MemberZoneID == $zoneID) {
                                    $zoneInstanceID = (integer)$zoneMember->InstanceID;
                                    // Zone power
                                    $zonePower = IPS_GetObjectIDByIdent('ZonePower', $zoneInstanceID);
                                    SetValue($zonePower, true);
                                    // Group
                                    $group = IPS_GetObjectIDByIdent('Group', $zoneInstanceID);
                                    SetValue($group, $groupNumber);
                                    // Group type
                                    $groupType = IPS_GetObjectIDByIdent('GroupType', $zoneInstanceID);
                                    SetValue($groupType, 'Slave');
                                }
                            }
                        }
                    }
                    $newZoneMembers = substr_replace($newZoneMembers, '+', $MemberZoneID, 1);
                    $endpoint = '/xml/zone/createGroup.xml?zone=' . $MemberZoneID . '&oldgroup=' . $masterZoneID . '&members=' . $newZoneMembers;
                    $this->SendData($endpoint);
                }
            }
        } else {
            $groupStatus = $this->GetGroupStatus();
            if (!is_null($groupStatus)) {
                $zones = $groupStatus->row;
                // Get group number of this zone
                $groupNumber = 255;
                foreach ($zones as $zone) {
                    $zoneID = (integer)$zone->id;
                    if ($zoneID == $masterZoneID) {
                        $groupNumber = (integer)$zone->group;
                    }
                }
                // Get grouped zone ids
                $groupedZoneMemberIDs = array();
                foreach ($zones as $zone) {
                    $id = (integer)$zone->id;
                    $group = (integer)$zone->group;
                    if ($group == $groupNumber) {
                        array_push($groupedZoneMemberIDs, $id);
                    }
                }
                $countMembers = count($groupedZoneMemberIDs);
                // Zone power
                $groupType = $this->GetValue('GroupType');
                // Zone power slave only
                if ($groupType == 'Slave') {
                    $this->SetValue('ZonePower', false);
                    $this->SetValue('Group', '');
                    $this->SetValue('GroupType', '');
                    if ($countMembers == 2) {
                        foreach ($zoneMembers as $zoneMember) {
                            $zoneInstanceID = $zoneMember->InstanceID;
                            $groupValue = GetValue(IPS_GetObjectIDByIdent('Group', $zoneInstanceID));
                            $groupType = GetValue(IPS_GetObjectIDByIdent('GroupType', $zoneInstanceID));
                            if ($groupType == 'Master' && $groupValue == $groupNumber) {
                                $group = IPS_GetObjectIDByIdent('Group', $zoneInstanceID);
                                SetValue($group, '');
                                $groupType = IPS_GetObjectIDByIdent('GroupType', $zoneInstanceID);
                                SetValue($groupType, '');
                            }
                        }
                    }
                }
                // Zone power all members
                if ($groupType == 'Master') {
                    $this->SetValue('Group', '');
                    $this->SetValue('GroupType', '');
                    if (!empty($groupedZoneMemberIDs) && $groupNumber != 255) {
                        foreach ($groupedZoneMemberIDs as $groupedZoneMemberID) {
                            foreach ($zoneMembers as $zoneMember) {
                                $zoneMemberID = (integer)$zoneMember->ZoneID;
                                if ($groupedZoneMemberID == $zoneMemberID) {
                                    $zoneInstanceID = $zoneMember->InstanceID;
                                    $powerZone = IPS_GetObjectIDByIdent('ZonePower', $zoneInstanceID);
                                    SetValue($powerZone, false);
                                    $group = IPS_GetObjectIDByIdent('Group', $zoneInstanceID);
                                    SetValue($group, '');
                                    $groupType = IPS_GetObjectIDByIdent('GroupType', $zoneInstanceID);
                                    SetValue($groupType, '');
                                }
                            }
                        }
                    }
                }
                // Dissolve group
                $newZoneMembers = '--------';
                $newZoneMembers = substr_replace($newZoneMembers, '+', $masterZoneID, 1);
                $endpoint = '/xml/zone/createGroup.xml?zone=' . $masterZoneID . '&oldgroup=' . $masterZoneID . '&members=' . $newZoneMembers;
                $this->SendData($endpoint);
            }
        }
    }

    /**
     * Gets the group status.
     *
     * @return null|SimpleXMLElement
     */
    public function GetGroupStatus()
    {
        $endpoint = '/xml/zone/getSelection.xml?grouped';
        $data = $this->SendData($endpoint);
        /*
        IPS_LogMessage('UBTFL_GroupInfo', json_encode($data));
        */
        return $data;
    }

    //#################### Private functions

    /**
     * Validates the configuration.
     */
    private function ValidateConfiguration()
    {
        $this->SetStatus(102);
        // Check zone name
        $zoneName = $this->ReadPropertyString('ZoneName');
        if ($zoneName == '') {
            $this->SetStatus(2221);
        } else {
            IPS_SetName($this->InstanceID, $zoneName);
        }
        // Check zone id
        $zoneID = $this->ReadPropertyInteger('ZoneID');
        if ($zoneID < 0) {
            $this->SetStatus(2211);
        }
        // Check timeout
        $timeout = $this->ReadPropertyInteger('Timeout');
        if ($timeout < 1000) {
            $this->SetStatus(2121);
        }
        // Check device ip-address
        $deviceIP = $this->ReadPropertyString('DeviceIP');
        if (empty($deviceIP)) {
            $this->SetStatus(2111);
        }
    }

    /**
     * Creates the audio sources profile.
     */
    private function CreateAudioSourcesProfile()
    {
        // Delete profile first
        $profile = 'UBTFL.' . $this->InstanceID . '.AudioSources';
        if (IPS_VariableProfileExists($profile)) {
            IPS_DeleteVariableProfile($profile);
        }
        // Create profile
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, 'Melody');
        IPS_SetVariableProfileValues($profile, 0, 0, 0);
        // Get favorite list
        $favorites = json_decode($this->ReadPropertyString('FavoriteList'));
        if (!empty($favorites)) {
            foreach ($favorites as $favorite) {
                $value = $favorite->Position;
                $text = $favorite->Description;
                // Create association
                if ($value != '' && $text != '') {
                    IPS_SetVariableProfileAssociation($profile, $value, $text, '', 0x000000);
                }
            }
        } else {
            IPS_SetVariableProfileAssociation($profile, -1, $this->Translate('None'), '', 0x0000FF);
        }
    }

    /**
     * Creates the audio favourites profile.
     */
    private function CreateAudioFavoritesProfile()
    {
        // Delete profile first
        $profile = 'UBTFL.' . $this->InstanceID . '.AudioFavorites';
        if (IPS_VariableProfileExists($profile)) {
            IPS_DeleteVariableProfile($profile);
        }
        // Create profile
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, 'Melody');
        IPS_SetVariableProfileValues($profile, 0, 0, 0);
        // Get favorite list
        $favorites = json_decode($this->ReadPropertyString('FavoriteList'));
        if (!empty($favorites)) {
            foreach ($favorites as $favorite) {
                $value = $favorite->Position;
                $text = $favorite->Favorite;
                // Create association
                if ($value != '' && $text != '') {
                    IPS_SetVariableProfileAssociation($profile, $value, $text, '', 0x000000);
                }
            }
        } else {
            IPS_SetVariableProfileAssociation($profile, -1, $this->Translate('None'), '', 0x000000);
        }
    }

    /**
     * Creates the zone member profile.
     */
    private function CreateZoneMembersProfile()
    {
        $zoneMembersProfile = 'UBTFL.' . $this->InstanceID . '.ZoneMembers';
        // Delete profile first
        if (IPS_VariableProfileExists($zoneMembersProfile)) {
            IPS_DeleteVariableProfile($zoneMembersProfile);
        }
        // Create profile
        IPS_CreateVariableProfile($zoneMembersProfile, 1);
        IPS_SetVariableProfileIcon($zoneMembersProfile, 'Execute');
        // Create first associations
        IPS_SetVariableProfileAssociation($zoneMembersProfile, -1, $this->Translate('Off'), '', 0x0000FF);
        // Get zone members list
        $zoneMembers = json_decode($this->ReadPropertyString('ZoneMembersList'));
        if (!empty($zoneMembers)) {
            foreach ($zoneMembers as $zoneMember) {
                $position = (integer)$zoneMember->Position;
                $zoneDescription = (string)$zoneMember->Description;
                if (!empty($zoneDescription)) {
                    // Create associations
                    IPS_SetVariableProfileAssociation($zoneMembersProfile, $position, '' . $zoneDescription . '', '', 0x0000FF);
                }
            }
        }
    }

    /**
     * Deletes the profiles.
     */
    private function DeleteProfiles()
    {
        // Zone power
        $zonePower = 'UBTFL.' . $this->InstanceID . '.ZonePower';
        if (IPS_VariableProfileExists($zonePower)) {
            IPS_DeleteVariableProfile($zonePower);
        }

        // Audio sources
        $audioSources = 'UBTFL.' . $this->InstanceID . '.AudioSources';
        if (IPS_VariableProfileExists($audioSources)) {
            IPS_DeleteVariableProfile($audioSources);
        }

        // Audio favorites
        $audioFavorites = 'UBTFL.' . $this->InstanceID . '.AudioFavorites';
        if (IPS_VariableProfileExists($audioFavorites)) {
            IPS_DeleteVariableProfile($audioFavorites);
        }

        // Volume slider
        $volumeSlider = 'UBTFL.' . $this->InstanceID . '.VolumeSlider';
        if (IPS_VariableProfileExists($volumeSlider)) {
            IPS_DeleteVariableProfile($volumeSlider);
        }

        // Zone members
        $zoneMembers = 'UBTFL.' . $this->InstanceID . '.ZoneMembers';
        if (IPS_VariableProfileExists($zoneMembers)) {
            IPS_DeleteVariableProfile($zoneMembers);
        }
    }

    /**
     * Sets the standard volume for an audio source.
     */
    private function SetStandardVolume()
    {
        $audioSource = $this->GetValue('AudioSources');
        $favorites = json_decode($this->ReadPropertyString('FavoriteList'));
        foreach ($favorites as $favorite) {
            if ($favorite->Position == $audioSource) {
                if ($favorite->Volume >= 0) {
                    $this->SetZoneVolume($favorite->Volume);
                }
            }
        }
    }

    /**
     * Checks if the device is alive.
     *
     * @return bool|null
     */
    private function CheckDevice()
    {
        $device = null;
        try {
            $deviceIP = $this->ReadPropertyString('DeviceIP');
            if (!empty($deviceIP)) {
                $timeout = $this->ReadPropertyInteger('Timeout');
                if ($timeout && Sys_Ping($deviceIP, $timeout) == true) {
                    $device = true;
                }
            }
            if (empty($device)) {
                $this->CreateMessageLogEntry($this->Translate("Unable to reach the device.\nPlease check network configuration!"));
            }
        } catch (Exception $e) {
            $this->CreateMessageLogEntry($e->getMessage());
        }
        return $device;
    }

    /**
     * Sends data to the endpoint of a Trivum device.
     *
     * @param string $Endpoint
     *
     * @return null|SimpleXMLElement
     */
    private function SendData(string $Endpoint)
    {
        $xmldata = null;
        try {
            $device = $this->CheckDevice();
            if (!empty($device)) {
                $deviceIP = $this->ReadPropertyString('DeviceIP');
                $timeout = $this->ReadPropertyInteger('Timeout');
                if ($timeout && Sys_Ping($deviceIP, $timeout) == true) {
                    $url = 'http://' . $deviceIP . $Endpoint;
                    $curl = curl_init();
                    curl_setopt_array($curl, array(CURLOPT_URL => $url,
                        CURLOPT_HEADER => 0,
                        CURLOPT_RETURNTRANSFER => 1,
                        CURLOPT_HTTPHEADER => array('Content-type: text/xml')));
                    $result = curl_exec($curl);
                    curl_close($curl);
                    if ($result !== false) {
                        $xmldata = new SimpleXMLElement($result);
                    }
                }
            }
        } catch (Exception $e) {
            $this->CreateMessageLogEntry($e->getMessage());
        }
        return $xmldata;
    }

    /**
     * Logs a message.
     * @param string $Text
     */
    protected function CreateMessageLogEntry(string $Text)
    {
        IPS_LogMessage('SymconTrivum', 'ID: ' . $this->InstanceID . ', ' . $Text);
        $webFront = IPS_GetInstanceListByModuleID(WEBFRONT_GUID)[0];
        WFC_SendNotification($webFront, $this->Translate('Error'), $Text, 'Warning', 10);
    }
}