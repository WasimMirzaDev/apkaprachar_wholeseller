 @if (Auth::guard('customer')->user()->is_reseller ?? 0 == 1)
    <a href="javascript:" class="{{ $class ?? 'btn btn-warning fs-16' }} btn-export exportProduct" title="Export Product" data-action="{{ route('product.export') }}"  data-product-id="{{ $product->id }}" >
        <i class="bi bi-download"></i>
    </a>
@endif