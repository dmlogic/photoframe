<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Photo;
use Illuminate\Console\Command;

class CreatePhotosFromJson extends Command
{
    protected $signature = 'photos:create_from_json';
    protected $failues = [];

    public function handle()
    {
        $files =  \Storage::disk('flickr')->files('/json');
        foreach($files as $file) {
            $info = pathinfo($file);
            if(substr($info['filename'], 0,6) !== 'photo_') {
                continue;
            }
            $detail = json_decode(\Storage::disk('flickr')->get($file));
            $this->addPhoto($detail);
        }
    }

    protected function addPhoto($photo)
    {
        $data = [
            'id'         => $photo->id,
            'title'      => $photo->name,
            'desc'       => $photo->description,
            'created_at' => new Carbon($photo->date_taken),
            'updated_at' => new Carbon($photo->date_imported),
        ];
        $cmd = sprintf("find %s -name '*%d*'",storage_path('flickr/files'),$photo->id);
        if(!$result = exec($cmd)) {
            $data['filename'] = $this->downloadFromFlickr($photo);
        } else {
            $bits = explode('/',$result);
            $data['filename'] = end($bits);
        }
        if(property_exists($photo, 'geo')) {
            $data['geo_latitude'] = $photo->geo->latitude;
            $data['geo_longitude'] = $photo->geo->longitude;
        }
        Photo::create($data);
    }

    protected function downloadFromFlickr($photo)
    {
        $filename = pathinfo($photo->original)['filename'];
        $filename = snake_case($photo->name).'_'.$photo->id.'.jpg';
        \Storage::disk('flickr')->put('/files/'.$filename,file_get_contents($photo->original));
        dump('Downloaded '.$photo->original);
        return $filename;
        // http://farm2.staticflickr.com/1621/26599349922_de1d1fac98_o.jpg
    }
}
