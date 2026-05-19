@php
    $block = $node['block'];
    $children = $node['children'] ?? [];
    $pl = $depth * 24;
@endphp

<div style="padding-left: {{ $pl }}px">
    <div class="document-block">@markdown($block->content)</div>
</div>

@if(!empty($children))
    <div class="d-flex flex-column gap-3 mt-2">
        @foreach($children as $child)
            @include('admin.documentos.partials.block', ['node' => $child, 'depth' => $depth + 1])
        @endforeach
    </div>
@endif
