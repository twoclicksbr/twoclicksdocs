@php
    $block = $node['block'];
    $children = $node['children'] ?? [];
    $pl = $depth * 16;
@endphp

<div style="padding-left: {{ $pl }}px">
    <div class="bg-tc-dark border border-tc-border rounded p-3 text-sm whitespace-pre-wrap break-words">{{ $block->content }}</div>
</div>

@if(!empty($children))
    @foreach($children as $child)
        @include('admin.documentos.partials.block', ['node' => $child, 'depth' => $depth + 1])
    @endforeach
@endif
