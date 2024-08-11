<?php

namespace App\Console\Commands;

use App\Jobs\ImportProductJob;
use Illuminate\Console\Command;

class ImportProductCommand extends Command
{
    protected $signature = 'import:product';

    protected $description = 'Command description';

    public function handle()
    {

        $filePath = base_path('example.csv');

        if (!file_exists($filePath)) {
            $this->error('Файл не существует!');
            return;
        }

        $fileHandle = fopen($filePath, 'r');

        if (!$fileHandle) {
            $this->error('Не удалось открыть файл для чтения.');
            return;
        }

        $isFirstIteration = true;

        while (($data = fgetcsv($fileHandle)) !== false) {
            if ($isFirstIteration) {
                $isFirstIteration = false;
                continue;
            }

            $imageURL = isset($data[8]) ? trim($data[8]) : '';

            $tags = explode(',', $data[7]); // Метки через запятую
            $tagsArray = array_map(function ($tag) {
                return ['name' => trim($tag)];
            }, $tags);

            $productData = [
                'sku' => $data[0],
                'name' => $data[1],
                'short_description' => $data[2],
                'description' => $data[3],
                'manage_stock' => true,
                'stock_quantity' => (int) $data[4],
                'sale_price' => $data[5],
                'regular_price' => $data[6],
                'tags' => $tagsArray,

                'images' => [
                    ['src' => $imageURL]
                ],
            ];

            ImportProductJob::dispatch($productData);

        }
        fclose($fileHandle);
    }
}
