<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Items;

class SearchController extends Controller {

    public function uploadcsv(Request $request) {
        $csvdata = $request->input('csvdata');
        $data = html_entity_decode($csvdata);
        $data = str_replace('&#39;', "'", $data);

        // Process CSV
        $csv = new \App\Helpers\csv();
        $csv->process($data);
        $csv->verifyHeaders();
        $lines = $csv->processLines();

        // write to database
        foreach ($lines as $line) {
            if ($line['error']) {
                continue;
            }
            $objectnumber = $line['object_number'];
            if (!$item = Items::where('object_number',  $objectnumber)->first()) {
                $item = new Items;
                $item->object_number = $objectnumber;
            }
            $item->institution_code = $line['institution_code'];
            $item->title = $line['title'];
            $item->object_category = $line['object_category'];
            $item->description = $line['description'];
            $item->reproduction_reference = $line['reproduction_reference'];
            $item->save();
        }

        return json_encode($csv->getErrors());
    }

    public function uploadzip(Request $request) {
        if ($request->hasFile('zipfile')) {
            $temppath = $request->zipfile->path();

            $zip = new \ZipArchive();
            if ($zip->open($temppath)) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $path =  $zip->statIndex($i);
                    $name = $path['name'];
                    Storage::disk('local')->put($name, $zip->getStream($name));
                }
            }
        }

        return 'done';
    }
}