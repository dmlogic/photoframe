<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ApplyMetaDescriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'photos:describe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $disk;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->disk = Storage::disk('flickr');
        $photos = Photo::with('album')->get();
        foreach($photos as $photo) {
            if(!$photo->album) {
                $this->error("no album ".$photo->filename);
                continue;
            }
            $path = 'albums/'.$photo->album->dirname.'/'.$photo->filename;
            if(!$this->disk->has($path)) {
                $this->error("bad path ".$path);
                continue;
            }
            $description = $this->createDescription($photo);
            $this->info($description);
            $this->applyDescription($description,$path);
        }
    }

    protected function applyDescription($description,$path)
    {
        $file = storage_path('flickr/'.$path);
        dump($file);
        $cmd = sprintf('exiftool "%s" -Description="%s"',$file,$description);
        exec($cmd);
        $this->disk->delete($path.'_original');
        // dd("stop");
    }

    protected function createDescription($photo)
    {
        $description = $photo->title;
        if($photo->desc) {
            $description .= '. '.$photo->desc;
        }
        return $description;
    }
}
