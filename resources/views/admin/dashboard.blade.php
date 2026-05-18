@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach([
        'projects'     => 'Projetos',
        'people'       => 'Pessoas',
        'users'        => 'Usuários',
        'documents'    => 'Documentos',
        'blocks'       => 'Blocos',
        'tasks'        => 'Tarefas',
        'task_details' => 'Ciclos de execução',
    ] as $key => $label)
        <div class="bg-tc-card border border-tc-border rounded p-4">
            <div class="text-xs uppercase text-gray-400">{{ $label }}</div>
            <div class="text-3xl font-bold mt-1">{{ $stats[$key] }}</div>
        </div>
    @endforeach
</div>
@endsection
