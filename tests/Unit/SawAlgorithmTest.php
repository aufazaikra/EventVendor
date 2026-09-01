<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SawService;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;

class SawAlgorithmTest extends TestCase
{
    private SawService $sawService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sawService = new SawService();
    }

    public function test_normalize_weights_is_correct()
    {
        // Misal input bobot dari form: Harga = 5, Rating = 3, Pengalaman = 2
        // Total = 10. w1 = 5/10 = 0.5, w2 = 3/10 = 0.3, w3 = 2/10 = 0.2
        $weights = $this->sawService->normalizeWeights(5, 3, 2);

        $this->assertEquals(0.5, $weights['w1']);
        $this->assertEquals(0.3, $weights['w2']);
        $this->assertEquals(0.2, $weights['w3']);
        $this->assertEquals(1.0, array_sum($weights));
    }

    public function test_saw_handles_zero_values_without_error()
    {
        // Test if divide by zero is protected
        $vendor = new Vendor();
        $vendor->id = 1;
        $vendor->pricelists_min_harga = 0;
        $vendor->reviews_avg_rating = 0;
        $vendor->transactions_count = 0;

        $vendors = new Collection([$vendor]);
        $weights = ['w1' => 0.5, 'w2' => 0.3, 'w3' => 0.2];

        $result = $this->sawService->calculate($vendors, $weights);

        $this->assertCount(1, $result);
        $this->assertEquals(0, $result->first()->skor);
        $this->assertEquals(0, $result->first()->n1);
        $this->assertEquals(0, $result->first()->n2);
        $this->assertEquals(0, $result->first()->n3);
    }

    public function test_saw_calculation_ranks_vendors_correctly()
    {
        /*
         * Skenario Simulasi:
         * Vendor A: Harga 100.000, Rating 5.0, Transaksi 50
         * Vendor B: Harga 200.000, Rating 4.0, Transaksi 20
         * Vendor C: Harga 500.000, Rating 3.0, Transaksi 5
         *
         * Max/Min Kolom:
         * min_harga = 100.000
         * max_rating_scale = 5.0 (Tetap dari konstanta sistem)
         * max_transaksi = 50
         *
         * Bobot: W1 = 0.5, W2 = 0.3, W3 = 0.2
         *
         * Perhitungan Vendor A:
         * n1 (Cost)    = 100.000 / 100.000 = 1.0
         * n2 (Benefit) = 5.0 / 5.0 = 1.0
         * n3 (Benefit) = 50 / 50 = 1.0
         * V (Skor) = (1.0 * 0.5) + (1.0 * 0.3) + (1.0 * 0.2) = 1.0
         *
         * Perhitungan Vendor B:
         * n1 (Cost)    = 100.000 / 200.000 = 0.5
         * n2 (Benefit) = 4.0 / 5.0 = 0.8
         * n3 (Benefit) = 20 / 50 = 0.4
         * V (Skor) = (0.5 * 0.5) + (0.8 * 0.3) + (0.4 * 0.2) = 0.25 + 0.24 + 0.08 = 0.57
         *
         * Perhitungan Vendor C:
         * n1 (Cost)    = 100.000 / 500.000 = 0.2
         * n2 (Benefit) = 3.0 / 5.0 = 0.6
         * n3 (Benefit) = 5 / 50 = 0.1
         * V (Skor) = (0.2 * 0.5) + (0.6 * 0.3) + (0.1 * 0.2) = 0.10 + 0.18 + 0.02 = 0.30
         */

        $vendorA = new Vendor();
        $vendorA->id = 1;
        $vendorA->name = 'Vendor A';
        $vendorA->pricelists_min_harga = 100000;
        $vendorA->reviews_avg_rating = 5.0;
        $vendorA->transactions_count = 50;

        $vendorA = new Vendor();
        $vendorA->id = 2;
        $vendorA->name = 'Vendor B';
        $vendorA->pricelists_min_harga = 200000;
        $vendorA->reviews_avg_rating = 4.0;
        $vendorA->transactions_count = 20;

        $vendorA = new Vendor();
        $vendorA->id = 3;
        $vendorA->name = 'Vendor C';
        $vendorA->pricelists_min_harga = 500000;
        $vendorA->reviews_avg_rating = 3.0;
        $vendorA->transactions_count = 5;

        // Kita acak urutannya untuk membuktikan algoritma bisa mengurutkan dengan benar
        $vendors = new Collection([$vendorB, $vendorC, $vendorA]);
        $weights = ['w1' => 0.5, 'w2' => 0.3, 'w3' => 0.2];

        $result = $this->sawService->calculate($vendors, $weights);

        // 1. Uji kebenaran ranking
        $this->assertEquals('Vendor A', $result[0]->data->name);
        $this->assertEquals('Vendor B', $result[1]->data->name);
        $this->assertEquals('Vendor C', $result[2]->data->name);

        // 2. Uji kebenaran perhitungan matematis
        $this->assertEquals(1.0, $result[0]->skor);
        $this->assertEquals(0.57, $result[1]->skor);
        $this->assertEquals(0.30, $result[2]->skor);
    }
}
