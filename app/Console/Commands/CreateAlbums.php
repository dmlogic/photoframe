<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Console\Command;

class CreateAlbums extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'albums:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $raw = json_decode( file_get_contents('/Users/darren/projects/photo_frame/transfer/storage/flickr/json/albums.json') );
        foreach($raw->albums as $album) {
            $this->createAlbum($album);
            $this->addPhotosTo($album);
        }
    }

    protected function addPhotosTo($album)
    {
        $i = 1;
        foreach($album->photos as $photo) {
            $affected = Photo::where('id',$photo)
                            ->update([
                                'album_id' => $album->id,
                                'sort_order' => $i
                                ]);
            if($affected) {
                $i++;
            }
        }

    }

    protected function createAlbum($album)
    {
        $data = [
            'id' => $album->id,
            'title' => $album->title,
            'created_at' => Carbon::createFromTimestamp($album->created),
            'updated_at' => Carbon::createFromTimestamp($album->last_updated)
        ];
        if($album->cover_photo) {
            $bits = explode('/',$album->cover_photo);
            $cover = end($bits);
            $data['cover_id'] = $cover;
        }
        return Album::create($data);
    }
}
