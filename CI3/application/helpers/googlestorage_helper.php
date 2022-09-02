<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;

if ( ! function_exists('upload_google'))
{
    function upload_google($path_upload, $file_name)
    {
        $storage = new  StorageClient ([
            'keyFile' => json_decode ( file_get_contents ( FCPATH.'/windy-hangar-321019-daae49ffa513.json' ), true ),
            'projectId' => 'windy-hangar-321019'
        ]);

        $source = $path_upload;
        if (file_exists($path_upload)){
            $file = fopen($source, 'r');
            $bucket = $storage->bucket('cron-veri-files-br');
            $bucket->upload($file, [
                'name' => $file_name
            ]);
        }
    }

    function upload_google_source($source, $file_name)
    {
        $storage = new  StorageClient ([
            'keyFile' => json_decode ( file_get_contents ( FCPATH.'/windy-hangar-321019-daae49ffa513.json' ), true ),
            'projectId' => 'windy-hangar-321019'
        ]);

        if (strlen($source) > 0){
            $bucket = $storage->bucket('cron-veri-files-br');
            $bucket->upload($source, [
                'name' => $file_name
            ]);
        }
    }

    function delete_file_google($objectName)
    {
        $storage = new  StorageClient ([
            'keyFile' => json_decode ( file_get_contents ( FCPATH.'/windy-hangar-321019-daae49ffa513.json' ), true ),
            'projectId' => 'windy-hangar-321019'
        ]);
        $bucket = $storage->bucket('cron-veri-files-br');
        $object = $bucket->object($objectName);
        if ($object){
            $object->delete();
        }
    }

}
