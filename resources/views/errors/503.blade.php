<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="10">
    <title>Atualizando o sistema — TwoClicks Docs</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
               font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
               background: #0B0C10; color: #f4f6fa; padding: 24px; }
        .box { max-width: 480px; text-align: center; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #f59e0b;
                 color: #0B0C10; font-size: 12px; font-weight: 600; letter-spacing: 0.5px;
                 text-transform: uppercase; margin-bottom: 24px; }
        h1 { font-size: 28px; font-weight: 600; margin: 0 0 12px; }
        p { font-size: 15px; line-height: 1.6; color: #c8ccd4; margin: 0 0 8px; }
        .hint { font-size: 13px; color: #7e8491; margin-top: 24px; }
        .spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid #444;
                   border-top-color: #f59e0b; border-radius: 50%; animation: spin 0.9s linear infinite;
                   vertical-align: middle; margin-right: 8px; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="box">
        <span class="badge">Manutenção</span>
        <h1>Atualizando o sistema</h1>
        <p><span class="spinner"></span>Estamos publicando uma nova versão. Volte em alguns instantes.</p>
        <p class="hint">Esta página recarrega sozinha a cada 10 segundos.</p>
    </div>
</body>
</html>
