<?php namespace Summer\Login\Models;

use Model;
use BackendAuth;
use Summer\Login\Classes\Skin;

/**
 * Model
 */
class Settings extends Model
{


    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'login_settings';
    public $settingsFields = 'fields.yaml';

    public function getActiveSkinOptions(){
        $skins_arr=[];
        $skins=Skin::all();
        foreach ($skins as $skin){
            $skins_arr[$skin->getDirName()]=[$skin->getConfigValue('name'),$skin->getPreviewImageUrl()];
        }
        return $skins_arr;
    }
}
