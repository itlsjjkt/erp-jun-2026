<?php

namespace Tests\Feature;

use App\Models\InventoryAsset;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class MasterAssetStatusTest extends TestCase
{
    use DatabaseTransactions;

    private function actingUser(): User
    {
        $user = User::orderBy('id')->firstOrFail();
        $user->givePermissionTo('inventory_asset');

        return $user;
    }

    private function makeAsset(int $status): InventoryAsset
    {
        return InventoryAsset::create([
            'doc_no'     => 'AST-TEST-' . uniqid(),
            'status'     => $status,
            'notes'      => 'catatan awal',
            'created_by' => 1,
        ]);
    }

    public function test_asset_draft_bisa_diupdate()
    {
        $asset = $this->makeAsset(0);

        $response = $this->actingAs($this->actingUser())
            ->put(route('logistic.inventory_asset.update', Hashids::encode($asset->id)), [
                'notes'         => 'catatan baru',
                'vendor_name'   => 'Vendor Test',
                'purchase_date' => '2026-07-01',
            ]);

        $response->assertSessionHas('success');
        $asset->refresh();
        $this->assertSame('catatan baru', $asset->notes);
        $this->assertSame('Vendor Test', $asset->vendor_name);
    }

    public function test_asset_aktif_tidak_bisa_diupdate()
    {
        $asset = $this->makeAsset(1);

        $response = $this->actingAs($this->actingUser())
            ->put(route('logistic.inventory_asset.update', Hashids::encode($asset->id)), [
                'notes' => 'coba ubah',
            ]);

        $response->assertSessionHas('error');
        $asset->refresh();
        $this->assertSame('catatan awal', $asset->notes);
    }

    public function test_activate_mengubah_draft_jadi_tersedia()
    {
        $asset = $this->makeAsset(0);

        $response = $this->actingAs($this->actingUser())
            ->post(route('logistic.inventory_asset.activate', Hashids::encode($asset->id)));

        $response->assertSessionHas('success');
        $asset->refresh();
        $this->assertSame(1, (int) $asset->status);
        $this->assertDatabaseHas('inventory_asset_histories', [
            'inventory_asset_id' => $asset->id,
            'type'               => 'publish',
        ]);
    }

    public function test_activate_ditolak_untuk_asset_non_draft()
    {
        $asset = $this->makeAsset(1);

        $response = $this->actingAs($this->actingUser())
            ->post(route('logistic.inventory_asset.activate', Hashids::encode($asset->id)));

        $response->assertSessionHas('error');
        $asset->refresh();
        $this->assertSame(1, (int) $asset->status);
    }
}
