<?php

use App\Models\Navigation;
use App\Models\Permission;


if (!function_exists('getMenus')) {
    function getMenus()
    {
        return Navigation::select('navigation.*')
            ->with('subMenus')->whereNull('main_menu')->whereNull('sub_menu')->orderby('navigation.sort')->get();
    }
}

if (!function_exists('setPermissions')) {
    function setPermissions()
    {
        return Permission::select('permissions.*')->with('subPermissions')->whereNull('main_permission')->orderby('permissions.name')->get();
    }
}
