<div class="dropdown">
  <button class="btn btn-outline border-dark float-right dropdown-toggle pR-10 pL-10" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Menu Master
  </button>
  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        @if ( Gate::allows('master_item'))
            <li><a class="dropdown-item" href="{{ route('master.items.index') }}"> Kategori Produk</a></li>
        @endif
        @if ( Gate::allows('master.product-action'))
            <li><a class="dropdown-item" href="{{ route('master.item_products.index') }}"> Produk</a></li>
        @endif
        @if ( Gate::allows('master_measure'))
            <li><a class="dropdown-item" href="{{ route('master.measures.index') }}"> Satuan</a></li>
        @endif
        @if ( Gate::allows('master_brand'))
            <li><a class="dropdown-item" href="{{ route('master.brands.index') }}"> Merk</a></li>
        @endif
        @if ( Gate::allows('master_expedition'))
            <li><a class="dropdown-item" href="{{ route('master.expeditions.index') }}"> Ekspedisi</a></li>
        @endif
  </div>
</div>
