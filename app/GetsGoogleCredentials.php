<?php

namespace App;

use Google\Auth\Credentials\UserRefreshCredentials;

trait GetsGoogleCredentials
{
    protected function getCredentials()
    {
        $path = base_path('google_access.json');
        if(!file_exists($path)) {
            return null;
        }
        $creds = unserialize(file_get_contents($path));
        if(!$creds instanceof UserRefreshCredentials) {
            return null;
        }
        return $creds;
    }
}