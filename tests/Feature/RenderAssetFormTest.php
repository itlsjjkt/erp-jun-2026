<?php

namespace Tests\Feature;

use App\Models\InventoryAsset;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class RenderAssetFormTest extends TestCase
{
    use DatabaseTransactions;

    private function actingUser(): User
    {
        $user = User::orderBy('id')->firstOrFail();
        $user->givePermissionTo('inventory_asset');

        return $user;
    }

    public function test_halaman_create_render()
    {
        $response = $this->actingAs($this->actingUser())
            ->get(route('logistic.inventory_asset.create'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Asset');
        $response->assertSee('Identitas &amp; Lokasi', false);
        $response->assertSee('CARA MENGISI');
    }

    public function test_halaman_edit_render()
    {
        $company = \DB::table('companies')->first();
        $asset = InventoryAsset::create([
            'doc_no'     => 'AST-RENDER-' . uniqid(),
            'status'     => 0,
            'company_id' => $company->id ?? null,
            'created_by' => 1,
        ]);

        $response = $this->actingAs($this->actingUser())
            ->get(route('logistic.inventory_asset.edit', Hashids::encode($asset->id)));

        $response->assertStatus(200);
        $response->assertSee('Simpan Perubahan');
        $response->assertSee('Status Saat Ini');
        $response->assertSee('Garansi s/d');
    }
}
