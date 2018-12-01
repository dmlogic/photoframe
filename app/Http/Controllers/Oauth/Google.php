<?php

namespace App\Http\Controllers\Oauth;

use Cache;
use Google\Auth\OAuth2;
use Illuminate\Http\Request;
use App\GetsGoogleCredentials;
use App\Http\Controllers\Controller;
use Google\Photos\Library\V1\PhotosLibraryClient;
use Google\Auth\Credentials\UserRefreshCredentials;

class Google extends Controller
{
    use GetsGoogleCredentials;

    protected $scope = ['https://www.googleapis.com/auth/photoslibrary'];

    public function test()
    {
        if(!$creds = $this->getCredentials()) {
            return redirect('/oauth/start');
        }
        $photosLibraryClient = new PhotosLibraryClient(['credentials' => $creds]);
        $pagedResponse = $photosLibraryClient->listAlbums();
        dd($pagedResponse);
    }

    public function start()
    {
        $client = $this->getCLient();
        return redirect($client->buildFullAuthorizationUri(['access_type' => 'offline']));
    }

    public function receiveCallback(Request $request)
    {
        $client = $this->getClient();
        $client->setCode($request->input('code'));
        $authToken    = $client->fetchAuthToken();
        $refreshToken = $authToken['access_token'];

        // The UserRefreshCredentials will use the refresh token to 'refresh' the credentials when
        // they expire.
        $credentials = new UserRefreshCredentials(
            $this->scope,
            [
                'client_id'     => $client->getClientId(),
                'client_secret' => $client->getClientSecret(),
                'refresh_token' => $refreshToken
            ]
        );
        file_put_contents(base_path('google_access.json'),serialize($credentials));
        return 'Credentials saved to cache';
    }



    protected function getClient($callback = '/oauth/callback')
    {
        $clientSecretJson = json_decode(
            file_get_contents(base_path('google_creds.json')),
            true
        )['web'];
        $clientId = $clientSecretJson['client_id'];
        $clientSecret = $clientSecretJson['client_secret'];
        $oauth2 = new OAuth2([
            'clientId'           => $clientSecretJson['client_id'],
            'clientSecret'       => $clientSecretJson['client_secret'],
            'authorizationUri'   => $clientSecretJson['auth_uri'],
            'redirectUri'        => 'http://localhost:8000'.$callback,
            'tokenCredentialUri' => 'https://www.googleapis.com/oauth2/v4/token',
            'scope'              => $this->scope,
        ]);
        return $oauth2;
    }
}