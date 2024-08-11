<?php

namespace App\Jobs;

use Automattic\WooCommerce\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $productData)
    {
        $this->productData = $productData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $woocommerce = new Client(
            'https://c2u.kg/',
            'ck_1d5f77c0006f943dec2ca81e950a1184bbe77a4b',
            'cs_63754884a7aa718b53b941c76e1a96a1d810090e',
            [
                'version' => 'wc/v3',
            ]
        );

        try {
            $existingProducts = $woocommerce->get('products', ['sku' => $this->productData['sku']]);
            if (!empty($existingProducts)) {
                Log::info('Product with SKU ' . $this->productData['sku'] . ' already exists. Skipping import.');
                return;
            }

            $product = $woocommerce->post('products', $this->productData);
            Log::info('Product imported: ' . $product->id);
        } catch (\Exception $e) {
            Log::error('Error importing product: ' . $e->getMessage());
        }
    }
}
