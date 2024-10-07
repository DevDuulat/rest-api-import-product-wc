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

        $filePath = base_path('example2.csv');

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

            $imageURL = isset($data[7]) ? trim($data[7]) : '';

            $tags = explode(',', $data[6]); // Метки через запятую
            $tagsArray = array_map(function ($tag) {
                return ['name' => trim($tag)];
            }, $tags);

            $sku = $this->generateSKU($data);

            $productData = [
                'sku' =>  $sku,
                'name' => $data[2],
                'short_description' => $data[3],
                'description' => $data[3],
//                'manage_stock' => true,
//                'stock_quantity' => (int) $data[4],
                'sale_price' => $data[4],
                'regular_price' => $data[5],
                'tags' => $tagsArray,
//
//                'images' => [
//                    ['src' => $imageURL]
//                ],
            ];

            ImportProductJob::dispatch($productData);

        }
        fclose($fileHandle);
    }

    /**
     * Генерация уникального SKU.
     *
     * @param array $data
     * @return string
     */
    protected function generateSKU(array $data): string
    {
        return 'SKU-' . strtoupper(substr($this->sanitizeString($data[2]), 0, 3)) . '-' . time();
    }

    protected function sanitizeString($string)
    {
        // Преобразуем строку в UTF-8 и удаляем некорректные символы
        $string = mb_convert_encoding($string, 'UTF-8', 'auto');
        return preg_replace('/[^\x20-\x7E]/', '', $string);
    }
}
