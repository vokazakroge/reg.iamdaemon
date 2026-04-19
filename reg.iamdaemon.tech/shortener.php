<?php
require_once __DIR__ . '/config.php';
requireLogin();
checkUserStatus();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener — DAEMON</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0a0f;
            --card: #12121a;
            --primary: #8b5cf6;
            --primary-hover: #7c3aed;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --border: #2a2a3a;
            --success: #10b981;
            --danger: #ef4444;
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding-top: 40px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 50px;
        }

        .logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }

        .back-btn {
            color: var(--muted);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }

        .back-btn:hover { 
            color: var(--text); 
        }

        h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.2rem;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
        }

        h2 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.3rem;
            margin-bottom: 25px;
            color: var(--text);
        }

        .input-group {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        input[type="text"] {
            flex: 1;
            padding: 14px 18px;
            background: rgba(139, 92, 246, 0.05);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            transition: 0.2s;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(139, 92, 246, 0.1);
        }

        button {
            padding: 14px 28px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.2s;
            font-family: 'Inter', sans-serif;
        }

        button:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        .result {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success);
            padding: 18px 20px;
            border-radius: 10px;
            margin-top: 20px;
            display: none;
            animation: fadeIn 0.3s;
        }

        .result.show { 
            display: block; 
        }

        .result strong {
            display: block;
            margin-bottom: 8px;
            color: var(--success);
        }

        .result a {
            color: var(--primary);
            font-weight: 700;
            word-break: break-all;
            text-decoration: none;
        }

        .result a:hover {
            text-decoration: underline;
        }

        .btn-copy {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--muted);
            padding: 8px 16px;
            font-size: 0.85rem;
            margin-left: 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-copy:hover {
            border-color: var(--primary);
            color: var(--text);
        }

        .urls-list {
            margin-top: 20px;
        }

        .url-item {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            transition: 0.2s;
        }

        .url-item:hover {
            border-color: var(--primary);
        }

        .url-info {
            flex: 1;
            min-width: 250px;
        }

        .url-info a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            display: block;
            margin-bottom: 5px;
            font-size: 1.05rem;
            word-break: break-all;
        }

        .url-info a:hover {
            text-decoration: underline;
        }

        .url-info small {
            color: var(--muted);
            word-break: break-all;
            font-size: 0.9rem;
        }

        .url-stats {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .stat {
            text-align: center;
        }

        .stat strong {
            display: block;
            font-size: 1.5rem;
            color: var(--primary);
            font-weight: 700;
        }

        .stat span {
            font-size: 0.85rem;
            color: var(--muted);
        }

        .btn-delete {
            background: var(--danger);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .btn-delete:hover { 
            background: #dc2626; 
        }

        .empty {
            text-align: center;
            padding: 50px;
            color: var(--muted);
            font-size: 1.1rem;
        }

        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(-10px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        @media (max-width: 768px) {
            .input-group {
                flex-direction: column;
            }
            
            .url-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .url-stats {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <a href="https://reg.iamdaemon.tech/dashboard" class="back-btn">
                <i class="fas fa-arrow-left"></i> Назад в Dashboard
            </a>
            <a href="https://iamdaemon.tech" class="logo">DAEMON</a>
        </header>

        <h1>✂️ Сокращатель ссылок</h1>

        <div class="card">
            <h2>Создать короткую ссылку</h2>
            <div class="input-group">
                <input type="text" id="longUrl" placeholder="Вставь длинную ссылку (https://...)">
                <button onclick="createShortUrl()">Сократить</button>
            </div>
            <div id="result" class="result">
                <strong>✅ Готово!</strong>
                Твоя короткая ссылка: 
                <a id="shortUrl" href="#" target="_blank"></a>
                <button class="btn-copy" onclick="copyUrl()">Копировать</button>
            </div>
        </div>

        <div class="card">
            <h2>Твои ссылки</h2>
            <div id="urlsList" class="urls-list">
                <div class="empty">Загрузка...</div>
            </div>
        </div>
    </div>

    <script>
        const username = '<?= $_SESSION['username'] ?>';

        function createShortUrl() {
            const longUrl = document.getElementById('longUrl').value;
            if (!longUrl) return alert('Введите URL');

            const formData = new FormData();
            formData.append('action', 'create');
            formData.append('long_url', longUrl);

            fetch('/api/shorten.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    document.getElementById('shortUrl').textContent = data.short_url;
                    document.getElementById('shortUrl').href = data.short_url;
                    document.getElementById('result').classList.add('show');
                    document.getElementById('longUrl').value = '';
                    loadUrls();
                }
            })
            .catch(e => alert('Ошибка: ' + e.message));
        }

        function loadUrls() {
            fetch('/api/shorten.php?action=list')
            .then(r => r.json())
            .then(data => {
                const container = document.getElementById('urlsList');
                if (data.urls.length === 0) {
                    container.innerHTML = '<div class="empty">У тебя пока нет ссылок</div>';
                    return;
                }

                let html = '';
                data.urls.forEach(url => {
                    html += `
                        <div class="url-item">
                            <div class="url-info">
                                <a href="${url.short_url}" target="_blank">${url.short_url}</a>
                                <small>${url.long_url}</small>
                            </div>
                            <div class="url-stats">
                                <div class="stat">
                                    <strong>${url.clicks}</strong>
                                    <span>переходов</span>
                                </div>
                                <button class="btn-delete" onclick="deleteUrl(${url.id})">Удалить</button>
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
            })
            .catch(e => {
                document.getElementById('urlsList').innerHTML = '<div class="empty" style="color: var(--danger);">Ошибка загрузки</div>';
            });
        }

        function deleteUrl(id) {
            if (!confirm('Удалить эту ссылку?')) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            fetch('/api/shorten.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) loadUrls();
                else alert(data.error);
            })
            .catch(e => alert('Ошибка: ' + e.message));
        }

        function copyUrl() {
            const url = document.getElementById('shortUrl').textContent;
            navigator.clipboard.writeText(url).then(() => {
                const btn = document.querySelector('.btn-copy');
                const original = btn.textContent;
                btn.textContent = 'Скопировано!';
                setTimeout(() => btn.textContent = original, 2000);
            });
        }

        // Загружаем список при старте
        loadUrls();
    </script>
</body>
</html>