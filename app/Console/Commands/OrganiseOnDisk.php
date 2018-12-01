<?php

namespace App\Console\Commands;

use Storage;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Console\Command;

class OrganiseOnDisk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'photos:organise';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move the images into Album folders';

    protected $disk;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->disk = Storage::disk('flickr');
        $this->renameToNewFormat();
    }

    protected function renameToNewFormat()
    {
        $raw = json_decode( file_get_contents('/Users/darren/projects/photo_frame/app/storage/albums.json') );
        foreach($raw->albums as $json) {
            // lookup album from DB
            $album = Album::findOrFail($json->id);
            // Make sure folder exists
            if(!$this->disk->has('albums/'.$album->dirname)) {
                $this->error('Cannot find'.$album->title.' '.$album->id);
                continue;
            }
            $newName = $json->title;
            if($json->description) {
                $newName .= ' - '.$json->description;
            }
            $this->info($album->dirname.' > '.$newName);
            if($this->disk->has('albums/'.$newName)) {
                $this->warn('Skipping '.$newName);
                continue;
            }
            $this->disk->move('albums/'.$album->dirname,'albums/'.$newName);
            $album->dirname = $newName;
            $album->save();
            // moveDirectory
            // // rename dir
            // // update DB
            // foreach($raw->albums as $album) {
            //     $this->info($album->title.' - '.$album->description);
            // }
        }
    }

    protected function saveToDirname()
    {
        $fixed = [72157600207033196,72157605503686145,72157608153549663,72157622700709644,72157623041666069,72157625100732579,72157625869254404,72157626716399237,72157627415166143,72157629181682463,72157632251658169,72157647066478682,72157647916518040,72157649792180219,72157650054406216,72157650109203122,72157652520539589,72157657594693486,72157663338920266,72157664183476888,72157666958129270,72157672582658233,72157672870739375,72157678281007956,72157680658838921,72157682109501994,72157683358688274,72157685527819940];
        // $query1 = Album::whereIn('id',$fixed)->get();
        // foreach($query1 as $album) {
        //     $expected = $album->title.' - '.$album->created_at->format('F Y');
        //     if(!$this->disk->has('albums/'.$expected)) {
        //         dd("missing $expected for ".$album->id);
        //     }
        //     $album->dirname = $expected;
        //     $album->save();
        // }
        $query2 = Album::whereNull('dirname')->get();
        foreach($query2 as $album) {
            $expected = $album->title;
            if(!$this->disk->has('albums/'.$expected)) {
                dd("missing $expected for ".$album->id);
            }
            $album->dirname = $expected;
            $album->save();
        }
        $this->info('ok');
    }

    protected function fixDuplicateAlbums()
    {
        $result = Album::whereIn('id',[72157600207033196,72157605503686145,72157608153549663,72157622700709644,72157623041666069,72157625100732579,72157625869254404,72157626716399237,72157627415166143,72157629181682463,72157632251658169,72157647066478682,72157647916518040,72157649792180219,72157650054406216,72157650109203122,72157652520539589,72157657594693486,72157663338920266,72157664183476888,72157666958129270,72157672582658233,72157672870739375,72157678281007956,72157680658838921,72157682109501994,72157683358688274,72157685527819940])
                        ->get();
        foreach($result as $album) {
            // Create a new album folder with the month and date
            $oldFolder  = $album->title;
            $newFolder = $album->title.' - '.$album->created_at->format('F Y');
            if(!$this->disk->has('albums/'.$newFolder)) {
                $this->disk->makeDirectory('albums/'.$newFolder);
            }
            // Move the photos from the old album
            $this->movePhotosAgain($album->id,$oldFolder,$newFolder);
        }
    }

    protected function movePhotosAgain($id,$oldFolder,$newFolder)
    {
        $photos = Photo::where('album_id',$id)->get();
        foreach($photos as $image) {
            $this->disk->move('albums/'.$oldFolder.'/'.$image->filename, 'albums/'.$newFolder.'/'.$image->filename);
        }
    }

    protected function loopThroughAlbums()
    {
        $albums  = Album::get();
        foreach ($albums as $album) {
            $this->info('flickr/'.$album->title);
            $this->disk->makeDirectory('albums/'.$album->title);
            $this->movePhotos($album);
        }
    }

    protected function movePhotos($album)
    {
        $photos = Photo::where('album_id',$album->id)->get();
        foreach($photos as $image) {
            $this->disk->move('files/'.$image->filename, 'albums/'.$album->title.'/'.$image->filename);
        }
    }
}
