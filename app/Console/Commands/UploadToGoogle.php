<?php

namespace App\Console\Commands;

use App\GetsGoogleCredentials;
use Illuminate\Console\Command;
use Google\Photos\Library\V1\Album;
use Google\Photos\Library\V1\PhotosLibraryClient;
use Google\Photos\Library\V1\PhotosLibraryResourceFactory;

use App\Models\Album as AlbumModel;

class UploadToGoogle extends Command
{
    use GetsGoogleCredentials;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'photos:to-google';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uploads photos to Google';

    protected $client;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(!$creds = $this->getCredentials()) {
            $this->error("Cannot get credentials");
            return;
        }
        $this->client = new PhotosLibraryClient(['credentials' => $creds]);
        $this->migrateAlbums();
    }

    protected function uploadPhoto()
    {
        $path = base_path('test.jpg');
        $uploadToken = $this->client->upload(file_get_contents($path), 'my-test.jpg');
        $newMediaItems = [];
        $newMediaItems[0] = PhotosLibraryResourceFactory::newMediaItemWithDescription($uploadToken, 'A detailed description');

        $response = $this->client->batchCreateMediaItems($newMediaItems,['albumId' => 'AMPTTLdz6Q0-0a1Q2itBNglS7I0s3LfsNzYaJHnzRXLoiNeSYtj7KIIbpvxr1IWy-YvIVUmr6Eur']);
        foreach ($response->getNewMediaItemResults() as $itemResult) {
            $status = $itemResult->getStatus();
            dump($status);
        }
    }

    protected function migrateAlbums()
    {
        // Get all not migrated
        $query = AlbumModel::whereNull('google_id')->orderBy('id','desc')->get();
        // Insert
        foreach($query as $albumRecord) {
            $this->info($albumRecord->dirname);
            $newAlbum = new Album;
            $newAlbum->setTitle($albumRecord->dirname);
            $createdAlbum = $this->client->createAlbum($newAlbum);
            $albumId = $createdAlbum->getId();
            $albumRecord->google_id = $albumId;
            $albumRecord->save();
            sleep(10);
        }
    }
}
