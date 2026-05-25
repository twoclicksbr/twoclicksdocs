<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Padroniza o slug do projeto "Docs TwoClicks" de "docs-twoclicks" pra
 * "docstwoclicks", batendo com a convenção dos outros 5 projetos (todos sem
 * separador: smartclick360, bethel360, apdireta, clickbank, whatspanel).
 *
 * Origem do bug: ProcessCodeTaskJob faz `config("twoclicks.tokens.{$slug}")`
 * sem normalizar. A config tem chave 'docstwoclicks' (já alinhada à convenção
 * sem hífen e à env TWOCLICKS_CODE_TOKEN_DOCSTWOCLICKS), mas o slug no DB
 * estava 'docs-twoclicks' — lookup falhava silenciosamente, job morria com
 * 'token não configurado'. Detectado na #88 ao validar fluxo fim-a-fim.
 *
 * Idempotente: só atualiza se o slug atual for o antigo. Permite rerun.
 * Nenhuma FK referencia slug como texto (FKs em tasks/documents/etc usam
 * project_id integer), então update é seguro.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('tc_doc')
            ->table('projects')
            ->where('slug', 'docs-twoclicks')
            ->update(['slug' => 'docstwoclicks']);
    }

    public function down(): void
    {
        DB::connection('tc_doc')
            ->table('projects')
            ->where('slug', 'docstwoclicks')
            ->where('name', 'Docs TwoClicks')
            ->update(['slug' => 'docs-twoclicks']);
    }
};
