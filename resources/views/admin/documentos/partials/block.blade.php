@php
    $block = $node['block'];
    $children = $node['children'] ?? [];
    $pl = $depth * 20;
@endphp

<div style="padding-left: {{ $pl }}px">
    <div class="p-4 rounded bg-light border border-dashed border-gray-300 fs-7 fw-semibold text-gray-700 whitespace-pre-wrap">{{ $block->content }}</div>
</div>

@if(!empty($children))
    <div class="d-flex flex-column gap-2 mt-2">
        @foreach($children as $child)
            @include('admin.documentos.partials.block', ['node' => $child, 'depth' => $depth + 1])
        @endforeach
    </div>
@endif
