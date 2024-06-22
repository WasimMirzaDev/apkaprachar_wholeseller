<?php

namespace App\Services;

class SettingService
{
    public function getVacationData(string $type): string
    {
        $url = '';
        foreach (config('addon_admin_routes') as $routeArray) {
            foreach ($routeArray as $route) {
                if ($route['name'] === $type) {
                    $url = $route['url'];
                    break 2;
                }
            }
        }
        return $url;
    }

}
